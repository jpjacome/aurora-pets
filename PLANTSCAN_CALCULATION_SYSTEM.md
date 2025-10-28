# PlantScan Calculation System - Original Plant Number Attribution

## Overview
The PlantScan system uses a **Jewish Gematria** numerical calculation combined with weighted lookup tables to assign each pet to one of 19 specific plants. Each plant has a unique number (not sequential, ranging from 1 to 27).

---

## Plant Number Mappings (19 Plants)

| Plant # | Plant Name | Family |
|---------|------------|--------|
| **1** | Pensamientos (Viola tricolor) | Violaceae |
| **3** | San Pedro | Cactaceae |
| **4** | Limonero | Rutaceae |
| **5** | Schefflera | Araliaceae |
| **6** | Monstera Deliciosa | Araceae |
| **7** | Buganvilla | Nyctaginaceae |
| **9** | Zamioculca | Araceae |
| **10** | Syngonium Neon Pink | Araceae |
| **12** | Sanseviera | Asparagaceae |
| **13** | Cala | Araceae |
| **14** | Syngonium Three Kings | Araceae |
| **15** | Anturio | Araceae |
| **17** | Calathea Triostar | Marantaceae |
| **18** | Monstera Adansonii | Araceae |
| **20** | Helecho nativo | Polypodiaceae |
| **21** | Capulí | Rosaceae |
| **22** | Jade | Crassulaceae |
| **23** | Syngonium Confettii | Araceae |
| **27** | Cholán | Tecoma |

**Note:** Numbers are not sequential (missing 2, 8, 11, 16, 19, 24, 25, 26). This is the original design.

---

## Calculation Method

### 1. Jewish Gematria Mapping
Each letter is assigned a numerical value:
```javascript
A: 1,   B: 2,   C: 3,   D: 4,   E: 5,   F: 6,   G: 7,   H: 8,   I: 9
J: 600, K: 10,  L: 20,  M: 30,  N: 40,  O: 50,  P: 60,  Q: 70,  R: 80
S: 90,  T: 100, U: 200, V: 700, W: 900, X: 300, Y: 400, Z: 500
```

### 2. Input Variables (10 Total)

#### **v1_owner** (NOT used in final calculation)
- Gematria sum of owner's name
- Example: "MARIA" = 30 + 1 + 80 + 9 + 1 = 121
- *Note: Excluded from final total per design decision*

#### **v2_pet**
- Gematria sum of pet's name
- Example: "LUNA" = 20 + 200 + 40 + 1 = 261

#### **v3_species**
- Pet species mapping:
```javascript
"perro":   13    "gato":    6     "conejo":  15
"hámster": 14    "pájaro":  11    "tortuga": 18
"pez":     29    "otro":    17
```

#### **v4_gender**
- Gender mapping:
```javascript
"masculino": 2
"femenino":  3
```

#### **v5_birthday**
- Birthday reduced to single digit (numerology):
  - Extract day from date
  - If day > 9, add digits until single digit
  - Example: Day 28 → 2+8 = 10 → 1+0 = 1

#### **v6_breed**
- Gematria sum of breed name
- Example: "GOLDEN RETRIEVER" (normalized to "GOLDENRETRIEVER")

#### **v7_weight**
- Weight range mapping:
```javascript
"1-5":   3
"5-10":  8
"10-15": 13
"15-25": 20
"25-35": 30
"35+":   35
```

#### **v8_colors**
- Sum of all selected colors:
```javascript
"rojo":     15   "azul":     13   "amarillo": 10
"verde":    14   "naranja":  5    "violeta":  21
"rosa":     6    "marrón":   7    "blanco":   1
"negro":    0
```

#### **v9_living**
- Living space mapping:
```javascript
"casa-jardin":      2
"casa-sin-jardin":  4
"departamento":     20
"finca-terreno":    7
```

#### **v10_virtues**
- Sum of all selected virtues:
```javascript
"fortaleza":      9    "dulzura":       6    "libertad":  0
"alegría":        4    "nobleza":       8    "independencia": 1
"energía":        9    "paz":           7    "protección": 2
"belleza":        6
```

### 3. Final Calculation

```javascript
// Sum all values (excluding v1_owner)
total = v2_pet + v3_species + v4_gender + v5_birthday + v6_breed + 
        v7_weight + v8_colors + v9_living + v10_virtues

// Map to plant using modulo
plantIndex = total % 19  // Results in 0-18
plantNumber = ALLOWED_PLANTS[plantIndex].num
```

### 4. Example Calculation

**Inputs:**
- Owner: "Maria" (not used)
- Pet: "Luna"
- Species: "gato"
- Gender: "femenino"
- Birthday: "2020-05-15" (day 15 → 1+5 = 6)
- Breed: "Persa"
- Weight: "5-10"
- Colors: ["blanco", "naranja"]
- Living: "departamento"
- Virtues: ["dulzura", "paz"]

**Calculation:**
```
v2_pet     = GEMATRIA("LUNA") = 20+200+40+1 = 261
v3_species = 6 (gato)
v4_gender  = 3 (femenino)
v5_birthday = 6 (day 15 → 1+5)
v6_breed   = GEMATRIA("PERSA") = 60+5+80+90+1 = 236
v7_weight  = 8 (5-10kg)
v8_colors  = 1 (blanco) + 5 (naranja) = 6
v9_living  = 20 (departamento)
v10_virtues = 6 (dulzura) + 7 (paz) = 13

total = 261 + 6 + 3 + 6 + 236 + 8 + 6 + 20 + 13 = 559
plantIndex = 559 % 19 = 7
ALLOWED_PLANTS[7] = { num: 10, name: "Syngonium Neon Pink" }
```

**Result:** Pet assigned to **Plant #10: Syngonium Neon Pink**

---

## Key Design Notes

1. **Non-sequential numbers:** Plants use specific numbers (1-27 with gaps) rather than 1-19 sequential
2. **Owner name excluded:** Originally included but removed from final calculation
3. **Modulo 19:** The 19 plants are stored in an array, and the total is mapped via `total % 19`
4. **Deterministic:** Same inputs always produce same plant
5. **Normalization:** All text normalized (uppercase, no accents, no spaces) before Gematria calculation

---

## Implementation Location

- **JavaScript:** `public/js/prevention.js`
  - `GEMATRIA` constant (lines 33-36)
  - `ALLOWED_PLANTS` array (lines 40-60)
  - Lookup tables (lines 62-76)
  - `computeAllValues()` function (lines 171-207)

- **Database:** Plants table stores these 19 plants with their official numbers
- **Backend:** Laravel controllers receive the calculated plant number from frontend

---

## Historical Context

This system was designed before the Plants table was added to the database. Originally, all calculation and plant selection occurred entirely on the frontend. The backend integration later added plant profiles, images, and descriptions while maintaining the original numbering system.
