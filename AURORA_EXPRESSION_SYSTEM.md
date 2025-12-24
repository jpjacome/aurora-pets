# Aurora Expression System Implementation

## Overview
Visual novel-style expression system for Aurora the cat chatbot. Shows 9 different expressions based on conversation context.

---

## Expression Grid Reference

```
     Column 1          Column 2           Column 3
   (Attentive)      (Sweet/Warm)        (Sad/Serious)

Row 1:  1-1            1-2               1-3
      Attentive      Sweet/           Deep Sadness
      Listening    Reminiscing

Row 2:  2-1            2-2               2-3
      Concerned/    Friendly         Focused/
      Gentle        Welcome          Serious

Row 3:  3-1            3-2               3-3
      Agreeing/     Neutral/        Compassionate
      Confirming    Processing
```

**Expression IDs:** `1-1`, `1-2`, `1-3`, `2-1`, `2-2`, `2-3`, `3-1`, `3-2`, `3-3`

---

## Scenario to Expression Mapping

| Scenario | Expression | Reason |
|----------|-----------|---------|
| **First Greeting** | `2-2` (Friendly Welcome) | Warm, inviting, neutral |
| **Emergency/Crisis** | `2-3` (Focused/Serious) | Professional, attentive to urgency |
| **Grief/Recent Loss** | `3-3` (Compassionate) | Deep empathy, holding space |
| **Preventive Planning** | `1-1` (Attentive/Listening) | Practical, listening carefully |
| **Casual Info** | `2-2` (Friendly Welcome) | Friendly educator |
| **B2B Inquiry** | `2-3` (Focused/Serious) | Professional, business-focused |
| **Happy/New Pet** | `1-2` (Sweet/Reminiscing) | Joyful, warm |
| **Confirming Info** | `3-1` (Agreeing/Confirming) | Supportive, affirming |
| **Processing Question** | `3-2` (Neutral/Processing) | Thoughtful, considering |
| **Deep Grief** | `1-3` (Deep Sadness) | Mirrors profound loss |
| **Concern/Warning** | `2-1` (Concerned/Gentle) | Gentle caution |

---

## Implementation Steps

### 1. Create Expression Detection Method

Add to `app/Services/GroqAIService.php`:

```php
/**
 * Detect appropriate cat expression based on conversation context
 */
private function detectExpression(string $userMessage, string $aiResponse, array $history): string
{
    $userLower = strtolower($userMessage);
    $aiLower = strtolower($aiResponse);
    
    // First message - friendly welcome
    if (count($history) === 0) {
        return '2-2'; // Friendly Welcome
    }
    
    // EMERGENCY/CRISIS - Focused/Serious
    $emergencyKeywords = ['urgente', 'acaba de', 'falleció', 'murió', 'tuvo un accidente', 'necesito ahora', 'ayuda urgente'];
    foreach ($emergencyKeywords as $keyword) {
        if (str_contains($userLower, $keyword)) {
            return '2-3'; // Focused/Serious
        }
    }
    
    // DEEP GRIEF - Deep Sadness or Compassionate
    $griefKeywords = ['perdí', 'se fue', 'ya no está', 'partió', 'lo extraño', 'la extraño', 'hace poco', 'ayer', 'anoche'];
    foreach ($griefKeywords as $keyword) {
        if (str_contains($userLower, $keyword)) {
            // If very recent or intense grief
            if (str_contains($userLower, 'ayer') || str_contains($userLower, 'anoche') || str_contains($userLower, 'hoy')) {
                return '1-3'; // Deep Sadness
            }
            return '3-3'; // Compassionate
        }
    }
    
    // HAPPY/NEW PET - Sweet/Warm
    $happyKeywords = ['nueva', 'nuevo', 'cachorro', 'gatito', 'bebé', 'bebé', 'adopté', 'feliz', 'alegre'];
    foreach ($happyKeywords as $keyword) {
        if (str_contains($userLower, $keyword)) {
            return '1-2'; // Sweet/Reminiscing
        }
    }
    
    // PREVENTIVE PLANNING - Attentive/Listening
    $preventiveKeywords = ['prepararme', 'plan preventivo', 'tiene', 'años', 'mayor', 'viejo', 'anciano', 'anticipar'];
    foreach ($preventiveKeywords as $keyword) {
        if (str_contains($userLower, $keyword)) {
            return '1-1'; // Attentive/Listening
        }
    }
    
    // B2B INQUIRY - Focused/Serious
    $b2bKeywords = ['veterinaria', 'veterinario', 'clínica', 'distribución', 'mayorista', 'negocio', 'empresa'];
    foreach ($b2bKeywords as $keyword) {
        if (str_contains($userLower, $keyword)) {
            return '2-3'; // Focused/Serious
        }
    }
    
    // QUESTIONS - Processing or Attentive
    if (str_contains($userLower, '?') || str_contains($userLower, 'cómo') || str_contains($userLower, 'qué')) {
        return '1-1'; // Attentive/Listening
    }
    
    // CONFIRMING/AGREEING - Agreeing/Confirming
    $confirmKeywords = ['sí', 'si,', 'está bien', 'ok', 'entiendo', 'gracias', 'perfecto', 'claro'];
    foreach ($confirmKeywords as $keyword) {
        if (str_contains($userLower, $keyword)) {
            return '3-1'; // Agreeing/Confirming
        }
    }
    
    // CONCERN/WARNING in AI response
    $concernKeywords = ['importante', 'recuerda', 'ten en cuenta', 'cuidado'];
    foreach ($concernKeywords as $keyword) {
        if (str_contains($aiLower, $keyword)) {
            return '2-1'; // Concerned/Gentle
        }
    }
    
    // DEFAULT - Friendly Welcome
    return '2-2'; // Friendly Welcome
}
```

### 2. Update analyzeConversation Method

Modify `analyzeConversation()` in `app/Services/GroqAIService.php` to include expression:

```php
private function analyzeConversation(string $userMessage, string $aiResponse, array $history): array
{
    $userLower = strtolower($userMessage);
    
    // Detect intent
    $intent = $this->detectIntent($userLower);
    
    // Calculate lead score based on conversation signals
    $leadScore = $this->calculateLeadScore($userLower, $history);
    
    // Determine if escalation is needed
    $shouldEscalate = $this->shouldEscalate($userLower);
    
    // Calculate confidence
    $confidence = $this->calculateConfidence($intent, count($history));
    
    // Detect appropriate expression
    $expression = $this->detectExpression($userMessage, $aiResponse, $history);

    return [
        'intent' => $intent,
        'lead_score' => $leadScore,
        'confidence' => $confidence,
        'should_escalate' => $shouldEscalate,
        'expression' => $expression, // NEW
    ];
}
```

### 3. Update Frontend to Display Expression

Modify `resources/views/admin/chatbot/test.blade.php`:

#### A. Add Aurora Image Container (above chat messages):

```html
<!-- Aurora Expression Display -->
<div class="aurora-expression-container">
    <img id="auroraExpression" 
         src="{{ asset('images/aurora-expressions/2-2.png') }}" 
         alt="Aurora" 
         class="aurora-expression-img">
    <div class="aurora-name">Aurora</div>
</div>

<!-- Chat Messages -->
<div id="chatMessages" class="chat-messages">
```

#### B. Add CSS for Expression Display:

```css
<style>
.aurora-expression-container {
    text-align: center;
    padding: 1rem;
    background: var(--color-2);
    border-bottom: 1px solid var(--color-1);
}

.aurora-expression-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid var(--color-1);
    object-fit: cover;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(254, 141, 44, 0.3);
}

.aurora-expression-img:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(254, 141, 44, 0.4);
}

.aurora-name {
    color: var(--color-3);
    font-size: 0.875rem;
    margin-top: 0.5rem;
    font-weight: 500;
}
</style>
```

#### C. Update JavaScript to Change Expression:

Find the `sendTestMessage()` function and update where insights are displayed:

```javascript
// Update insights panel
document.getElementById('intentValue').textContent = data.insights.intent;
document.getElementById('leadScoreValue').textContent = data.insights.lead_score;
document.getElementById('confidenceValue').textContent = (data.insights.confidence * 100).toFixed(0) + '%';
document.getElementById('escalateValue').textContent = data.insights.should_escalate ? 'Yes' : 'No';

// NEW: Update Aurora expression
if (data.insights.expression) {
    const expressionImg = document.getElementById('auroraExpression');
    expressionImg.src = `/images/aurora-expressions/${data.insights.expression}.png`;
    
    // Add animation
    expressionImg.style.opacity = '0.7';
    setTimeout(() => {
        expressionImg.style.opacity = '1';
    }, 150);
}
```

### 4. Create Expression Images

Create 9 images of Aurora (small orange cat) with different expressions:

**Image Specifications:**
- Format: PNG with transparent background
- Size: 300x300px minimum (square)
- Style: Consistent art style (illustration, vector, or photo)
- Naming: `1-1.png`, `1-2.png`, `1-3.png`, etc.
- Location: `public/images/aurora-expressions/`

**Expression Descriptions for Artist:**
- `1-1.png`: Attentive, ears perked, focused eyes
- `1-2.png`: Sweet, gentle smile, warm eyes
- `1-3.png`: Deep sadness, downcast eyes, somber
- `2-1.png`: Concerned, slightly worried, caring
- `2-2.png`: Friendly welcome, bright eyes, inviting
- `2-3.png`: Focused, serious, professional
- `3-1.png`: Agreeing, nodding, supportive
- `3-2.png`: Neutral, processing, thoughtful
- `3-3.png`: Compassionate, empathetic, holding space

### 5. Create Directory and Placeholder

```bash
# Create directory
New-Item -ItemType Directory -Path "public/images/aurora-expressions" -Force

# For now, use a placeholder
# You can use a default cat image or solid color while getting expressions designed
```

---

## Testing Expression System

### Test Cases:

1. **First message**: Should show `2-2` (Friendly Welcome)
   - Send: "hola"

2. **Emergency**: Should show `2-3` (Focused/Serious)
   - Send: "Mi perro acaba de morir, necesito ayuda urgente"

3. **Grief**: Should show `3-3` (Compassionate)
   - Send: "Perdí a mi gata hace una semana"

4. **Happy pet**: Should show `1-2` (Sweet)
   - Send: "Tengo una nueva perrita Laura"

5. **Preventive**: Should show `1-1` (Attentive)
   - Send: "Mi perra tiene 13 años, quiero prepararme"

6. **Questions**: Should show `1-1` (Attentive)
   - Send: "¿Cómo funciona el servicio?"

7. **Confirmation**: Should show `3-1` (Agreeing)
   - Send: "Sí, entiendo. Gracias"

---

## WhatsApp Integration (Future)

For WhatsApp, expressions would be included in webhook responses:

```json
{
  "response": "¡Buenos días! 🧡 El servicio Aurora...",
  "expression": "2-2",
  "should_escalate": false
}
```

The WhatsApp interface would display the appropriate expression image alongside the text.

---

## Benefits

✅ **Enhanced Personality**: Visual feedback makes Aurora feel more alive
✅ **Emotional Intelligence**: Expressions match conversation mood
✅ **User Engagement**: Visual interest keeps users engaged
✅ **Brand Consistency**: Reinforces Aurora character identity
✅ **Empathy Signals**: Shows Aurora "understands" the emotional context

---

## Next Steps

1. ✅ Add `detectExpression()` method to GroqAIService
2. ✅ Update `analyzeConversation()` to return expression
3. ✅ Modify test.blade.php HTML/CSS for expression display
4. ✅ Update JavaScript to change expressions dynamically
5. ⏳ Create/commission 9 Aurora expression images
6. ⏳ Test with various conversation scenarios
7. ⏳ Deploy to server
8. ⏳ Integrate into WhatsApp interface (future)
