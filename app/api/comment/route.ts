import { NextRequest, NextResponse } from 'next/server'

const TAVUS_API_KEY = process.env.TAVUS_API_KEY!

// Maps each Typeform field ref to the educational comment the AI should speak.
// These are keyed to the exact field refs from the Cardio Quiz (form ID: ENn7cxWK).
const QUESTION_COMMENTS: Record<string, string> = {
  // Opening clinical statement — introduce the context
  'c23b08c6-db35-4101-930f-57c23d98d87a':
    "Thank you for beginning this assessment. I want to highlight that African American patients often present with cardiovascular disease earlier and more severely than other populations, so a thorough screening is especially valuable here.",

  // Q1 — chest pain
  '5ee166cc-711e-4dfb-9a83-f7c65b70d763':
    "Exertional chest pain or pressure is one of the most critical warning signs of coronary artery disease. In African American patients, atypical presentations are more common, so even vague chest discomfort deserves careful evaluation rather than dismissal.",

  // Q2 — risk factors: HTN, DM, high cholesterol, CKD
  '573aa76e-3ab2-4750-99e5-43446ed5d6a7':
    "Hypertension affects African Americans at a significantly higher rate and tends to be more severe — often requiring more aggressive management. When combined with diabetes or chronic kidney disease, the compounding effect on cardiovascular risk is substantial.",

  // Q3 — dyspnea, reduced exercise tolerance, dizziness, syncope
  'c6b5ae72-3ad0-44df-b3c7-b348d3ceda96':
    "Shortness of breath and reduced exercise tolerance are often early signs of heart failure, which has a disproportionate impact on African American patients. Syncope or near-syncope may indicate arrhythmia and warrants prompt evaluation.",

  // Q4 — family history of premature CVD
  '462fc153-3aac-417e-88d3-b2a10de33213':
    "A family history of premature cardiovascular disease is a powerful independent risk factor — it suggests both genetic predisposition and shared environmental risks. When present alongside other risk factors in this population, it substantially elevates the urgency of cardiology referral.",

  // Q5 — palpitations, edema, unexplained fatigue
  '3d5c3bb1-d2f1-40d4-9de2-365b3bdfaf2a':
    "Palpitations can signal arrhythmia, while lower extremity edema may indicate reduced cardiac output. Unexplained fatigue is frequently underreported as a cardiac symptom in African American patients — taken together, these signs in a high-risk patient support timely cardiology referral.",

  // Thank you screen — quiz complete
  'default_tys':
    "The Cardio Quiz is now complete. If the patient answered yes to two or more questions, I strongly recommend a cardiology referral given the elevated risk profile. Remember, early intervention in African American patients can be especially impactful given the tendency toward earlier onset of disease. Thank you for using this screening tool.",
}

export async function POST(req: NextRequest) {
  try {
    const { conversationId, questionRef } = await req.json()

    if (!conversationId || !questionRef) {
      return NextResponse.json({ error: 'conversationId and questionRef are required' }, { status: 400 })
    }

    const comment = QUESTION_COMMENTS[questionRef]
    if (!comment) {
      // Unknown ref — silently skip, don't break the UI
      return NextResponse.json({ skipped: true })
    }

    // Inject the comment into the active Tavus conversation.
    // Tavus CVI supports real-time context injection via the /echo endpoint.
    // If your Tavus plan uses a different endpoint (e.g. /say or /overrideText),
    // update the URL below — check https://docs.tavus.io for your plan's API.
    const tavusRes = await fetch(
      `https://tavusapi.com/v2/conversations/${conversationId}/echo`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'x-api-key': TAVUS_API_KEY,
        },
        body: JSON.stringify({ message: comment }),
      }
    )

    if (!tavusRes.ok) {
      const errText = await tavusRes.text()
      console.error('Tavus echo error:', errText)
      // Don't fail the request — the UI can continue without the AI comment
      return NextResponse.json({ warning: 'Tavus echo failed', detail: errText })
    }

    return NextResponse.json({ ok: true })
  } catch (err) {
    console.error('comment route error:', err)
    return NextResponse.json({ error: 'Internal error' }, { status: 500 })
  }
}
