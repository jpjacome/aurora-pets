# PlantScan Deterministic Selection — Implementation Plan

Date: 2025-12-23

## Goal 🎯
Make PlantScan selection deterministic and persistent when there are multiple plants sharing the same `plant_number` in the database. Identical test answers (inputs) must always map to the same plant, even if the candidate ordering changes or additional unrelated plants are added elsewhere in the DB.

This document describes the algorithm, data model, migrations, code entries, tests, QA, roll-out and rollback procedures in full, with examples and acceptance criteria.

---

## High-level approach (recommended): Answer-hash index (default strategy)

1. Normalize test inputs deterministically (fields used by frontend to compute the total):
   - Fields: `pet_name`, `pet_species`, `gender`, `pet_birthday`, `pet_breed`, `pet_weight`, `pet_color` (sorted list), `living_space`, `pet_characteristics` (sorted list), `virtues` (selected items), and optionally `owner_name` if using it.
   - Normalization rules (MUST be consistent across front-end and back-end): trim, lowercase, NFKC unicode normalization, remove punctuation, normalize whitespace, remove diacritics (á → a), sort array values, and stringify in canonical ordering.

2. Compute stable hash:
   - `normalized = implode('|', [field1, field2, ...])`
   - `hash = sha1(config('plantscan.selection_salt') . '|' . normalized)`
   - `index = hexdec(substr($hash, 0, 12)) % count($candidates)`

3. Deterministic candidate ordering: sort candidates by `id` or `slug` (use `id` for stability across environments).

4. Chosen plant = `candidates[$index]`.

5. Persist the selection metadata with the test row (see Data Model) and set the Pet's `plant_id` to the chosen plant's id.

6. Return the chosen `plant_id` to client (email generation uses persisted metadata) and include `selection_version` & `selection_strategy` for auditability.

---

## Alternative / Hybrid option

- Semantic scoring (tag-based) + deterministic tiebreaker: compute a score for each candidate using plant tags/keywords, if tie -> fallback to hash-index. This yields more semantically relevant choices but needs tag data and extra maintenance.

---

## Data model & migrations

### Minimal changes (preferred)
- Use existing `tests.metadata` json column to store a `selection` object. This avoids DB schema changes while still making the selection auditable. Example structure:

```json
"selection": {
  "version": "1",
  "strategy": "hash-index",
  "salt_hash": "sha1-of-salt-version-or-id",
  "inputs_hash": "sha1(normalized-inputs)",
  "chosen_plant_id": 123,
  "candidate_ids": [45, 123, 900],
  "algorithm_params": { "index": 1, "candidates_count": 3 }
}
```

### Optional DB columns (if you need querying / indexing)
- Add `selection_meta` JSON column to `tests` with index on `selection->chosen_plant_id` (or add `chosen_plant_id` int column). Create migration file `2025_12_23_add_selection_meta_to_tests.php`.

### Notes on PII
- Avoid storing full answers or large personal data in cleartext selection objects. Instead, store normalized inputs hash and minimal candidate list. If storing a sample of inputs is required for audit, ensure compliance and encryption where necessary.

---

## Service & API design

### Service: `App\Services\PlantScanSelectionService`
- Public method: `selectPlant(int $plantNumber, array $inputs, array $options = []): ?Plant`.
- Also provide `computeNormalizedInputs(array $inputs): string` and `computeIndex(string $normalized, int $count, string $salt): int` helpers.
- Accept `strategy` parameter (`hash-index`, `semantic-score`) and `version`.
- Example usage in `TestController::store()`:

```php
$service = new PlantScanSelectionService();
$chosen = $service->selectPlant($request->input('plant_number'), $request->only([...]));
// Persist selection and set pet->plant_id
```

### Normalization utility
- Implement `App\Services\PlantScanSelectionService::normalizeInputs()` using PHP `Normalizer::normalize()` (NFKC), `mb_strtolower`, `transliterator_transliterate('NFD; [:Nonspacing Mark:] Remove; NFC')` to strip diacritics, `preg_replace` to remove punctuation, and sorting arrays.

---

## Controller changes & persistence

- `TestController::store()` should:
  1. Validate inputs (no changes to current validation rules).
  2. Create test row (as today) but delay setting `plant`/`plant_description` until after selection.
  3. If `plant_number` is present:
     - Fetch candidates: `$candidates = Plant::where('plant_number', $num)->where('is_active', true)->orderBy('id')->get();`
     - If count <= 1: choose that plant (same as current behaviour)
     - Else: call selection service to choose `Plant` deterministically
     - Persist selection metadata in `test->metadata['selection']`
     - Set/ensure `pet->plant_id` and `pet->plant_test` fields are set using chosen plant
  4. Ensure email generation and later profile views read the persisted chosen plant (ProfileController already prefers `pet->plant_id` if present)

---

## Tests (obsessive list) ✅

### Unit Tests
- `PlantScanSelectionServiceTest::test_same_inputs_same_choice()` — assert same inputs result in same chosen plant over many iterations.
- `...::test_ordering_change_does_not_affect_choice()` — reorder candidates and assert same outcome.
- `...::test_single_candidate_short_circuit()` — single candidate returns it.
- `...::test_no_candidates_returns_null()` — returns null.
- `...::test_salt_change_changes_index()` — assert changing salt/version changes mapping.

### Integration Tests
- `TestControllerSelectionTest::test_end_to_end_selection_persists()` — POST to `/tests` with test inputs and assert `tests.metadata.selection` fields exist and `pet->plant_id` set.
- `...::test_historical_recompute()` — simulate backfill: compute selection for historical records and assert idempotence.

### Regression Tests
- Add a fixture with two plants having same `plant_number` and repeated identical answers; the test must always pick the same plant, even if candidate DB order is changed.

---

## Backfill plan

1. Create an Artisan command `plantscan:backfill-selection --dry-run` that:
   - Finds tests with `plant_number IS NOT NULL` and `metadata->selection` missing OR missing `chosen_plant_id`.
   - For each row, reconstruct inputs from `tests` row and compute chosen plant using the new service.
   - Optionally write the selection metadata and, if desired, update `pet->plant_id` for pets with no `plant_id`.
2. Run dry-run, produce report: how many rows would change, conflicts.
3. After verification and DB backups, run without `--dry-run` to persist.

---

## Logging, metrics and monitoring

- Log each selection at INFO level with structured fields: `test_id`, `chosen_plant_id`, `plant_number`, `candidates_count`, `selection_hash` (not raw inputs), `selection_version`.
- Track a metric `plantscan.ambiguous_selection_count` and `plantscan.selection_distribution` to observe how often collisions occur and which plant_numbers are frequently ambiguous.
- Alert if ambiguous selection percentage changes rapidly after deploy.

---

## Deployment & QA checklist

1. Create feature branch with service, tests, migration and updated `TestController`.
2. Open PR with clear description and link to this doc.
3. Run unit & integration tests locally & in CI.
4. Deploy to staging, run the backfill in dry-run on staging DB and inspect results.
5. Manual QA: pick test inputs that map to ambiguous numbers and assert persistent mapping across runs and across simultaneous reorders.
6. Take DB backup (production) before migration.
7. Deploy to production, run migrations, run backfill (careful - default dry-run first), monitor logs/metrics.
8. If rollback necessary: revert code, restore DB from backup, or remove selection metadata migration (depending on scope).

---

## Acceptance criteria ✅
- Deterministic selection service passes unit tests (repeatability, stability).
- Integration tests show persisted selection metadata and pet->plant_id set.
- Backfill dry-run shows expected mapping and no data corruption.
- Staging manual QA passes: same inputs → same plant; mapping stable when candidate ordering changes.

---

## Follow-ups / Enhancements
- Add semantic scoring metadata for plants (tags) and implement scoring-based selection with deterministic tiebreaker.
- Add admin UI to manage candidate preferences or mark a plant as `preferred` for a specific `plant_number`.
- Add a migration that stores a canonical `mapping_id` per `plant` for faster candidate lookup.

---

## Timeline estimate (rough)
- Design & doc + small PR: 0.5 days
- Core service + unit tests: 1 day
- Controller integration + integration tests: 0.5 day
- Backfill script + dry-run verification: 0.5 day
- Staging deployment & QA: 0.5 day
- Production deploy & backfill: 0.5 day

Total: ~3.5 days of focused work (with testing & verification)

---

If you want, I can now: (A) implement the service and tests on a feature branch and open a PR, or (B) just leave this document as the authoritative plan and wait for your approval. Please tell me which you'd prefer.