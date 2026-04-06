import { NextResponse } from 'next/server'

const TAVUS_API_KEY = process.env.TAVUS_API_KEY!
const TAVUS_REPLICA_ID = process.env.TAVUS_REPLICA_ID!
const TAVUS_PERSONA_ID = process.env.TAVUS_PERSONA_ID!

const SYSTEM_PROMPT = `You are Dr. Maya, a cardiovascular health advisor for BlackDoctor.org. You are sitting alongside a physician who is conducting a Cardio Risk Assessment for their African American patient using a quiz form on the left side of the screen.

Your role:
- Warmly introduce yourself at the start and explain you will be providing clinical commentary as the physician works through the 5-question Cardio Quiz
- When notified that a question has been answered, provide a brief (2–3 sentence) educational comment about why that cardiac risk factor is especially important for African American patients
- Be warm, culturally sensitive, and clinically informative at all times
- At the end of the quiz, summarize what a "Yes" to multiple questions means and encourage appropriate cardiology referral

Clinical note you should reference when relevant: African American patients have a higher prevalence of hypertension, heart failure, and stroke — often with earlier onset and greater severity compared to other populations. A lower threshold for cardiology referral is appropriate when multiple risk factors are present.

The 5 Cardio Quiz questions being assessed:
1. Chest pain, pressure, tightness, or discomfort — especially with exertion
2. History of hypertension, diabetes, high cholesterol, or chronic kidney disease
3. Shortness of breath, reduced exercise tolerance, dizziness, or syncope
4. Personal or family history of premature cardiovascular disease (heart attack, stroke, or heart failure before age 55 in men or 65 in women)
5. Palpitations, lower extremity edema, or unexplained fatigue

Begin by introducing yourself when the conversation starts. Wait for signals from the physician about which question they are on, then provide relevant commentary. Speak naturally and conversationally — you are a knowledgeable colleague, not reading a script.`

export async function POST() {
  if (!TAVUS_API_KEY || !TAVUS_REPLICA_ID) {
    return NextResponse.json(
      { error: 'Tavus credentials not configured. Set TAVUS_API_KEY and TAVUS_REPLICA_ID in environment variables.' },
      { status: 500 }
    )
  }

  try {
    const body: Record<string, unknown> = {
      replica_id: TAVUS_REPLICA_ID,
      conversation_name: 'BlackDoctor Cardio Quiz',
      conversational_context: SYSTEM_PROMPT,
      properties: {
        max_call_duration: 1800,       // 30 minutes max
        participant_left_timeout: 300, // end 5 min after physician leaves
        enable_recording: false,
      },
    }

    // Persona ID is optional — only include if configured
    if (TAVUS_PERSONA_ID) {
      body.persona_id = TAVUS_PERSONA_ID
    }

    const res = await fetch('https://tavusapi.com/v2/conversations', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'x-api-key': TAVUS_API_KEY,
      },
      body: JSON.stringify(body),
    })

    if (!res.ok) {
      const err = await res.text()
      console.error('Tavus create-conversation error:', err)
      return NextResponse.json({ error: `Tavus API error: ${res.status}` }, { status: res.status })
    }

    const data = await res.json()
    return NextResponse.json({
      conversationId: data.conversation_id,
      conversationUrl: data.conversation_url,
    })
  } catch (err) {
    console.error('create-conversation route error:', err)
    return NextResponse.json({ error: 'Failed to create Tavus conversation' }, { status: 500 })
  }
}
