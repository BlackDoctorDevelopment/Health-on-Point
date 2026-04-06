'use client'

import { useEffect, useRef, useState, useCallback } from 'react'
import { Widget } from '@typeform/embed-react'

// ─── Question metadata ────────────────────────────────────────────────────────
// Matches the exact field refs from the Cardio Quiz (form ENn7cxWK)
const QUESTION_LABELS: Record<string, string> = {
  'c23b08c6-db35-4101-930f-57c23d98d87a': 'Clinical Context',
  '5ee166cc-711e-4dfb-9a83-f7c65b70d763': 'Chest Pain / Discomfort',
  '573aa76e-3ab2-4750-99e5-43446ed5d6a7': 'Cardiovascular Risk Factors',
  'c6b5ae72-3ad0-44df-b3c7-b348d3ceda96': 'Symptoms: Dyspnea / Syncope',
  '462fc153-3aac-417e-88d3-b2a10de33213': 'Family History',
  '3d5c3bb1-d2f1-40d4-9de2-365b3bdfaf2a': 'Signs of Cardiac Involvement',
  'default_tys': 'Assessment Complete',
}

const QUESTION_ORDER = [
  'c23b08c6-db35-4101-930f-57c23d98d87a',
  '5ee166cc-711e-4dfb-9a83-f7c65b70d763',
  '573aa76e-3ab2-4750-99e5-43446ed5d6a7',
  'c6b5ae72-3ad0-44df-b3c7-b348d3ceda96',
  '462fc153-3aac-417e-88d3-b2a10de33213',
  '3d5c3bb1-d2f1-40d4-9de2-365b3bdfaf2a',
]

type TavusState = 'idle' | 'loading' | 'ready' | 'error'

export default function CardioQuizPage() {
  const [tavusState, setTavusState] = useState<TavusState>('idle')
  const [conversationUrl, setConversationUrl] = useState<string | null>(null)
  const conversationIdRef = useRef<string | null>(null)

  const [currentRef, setCurrentRef] = useState<string>(QUESTION_ORDER[0])
  const [completed, setCompleted] = useState(false)
  const [aiStatus, setAiStatus] = useState<string>('AI advisor will comment as you progress')

  // ── Create Tavus conversation on mount ───────────────────────────────────
  useEffect(() => {
    async function startConversation() {
      setTavusState('loading')
      try {
        const res = await fetch('/api/create-conversation', { method: 'POST' })
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        const data = await res.json()
        conversationIdRef.current = data.conversationId
        setConversationUrl(data.conversationUrl)
        setTavusState('ready')
      } catch (err) {
        console.error('Failed to start Tavus conversation:', err)
        setTavusState('error')
      }
    }
    startConversation()
  }, [])

  // ── Inject a comment into the Tavus AI when a question changes ───────────
  const injectComment = useCallback(async (questionRef: string) => {
    const id = conversationIdRef.current
    if (!id) return

    setAiStatus(`Dr. Maya is commenting on: ${QUESTION_LABELS[questionRef] ?? 'current question'}…`)

    try {
      await fetch('/api/comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ conversationId: id, questionRef }),
      })
      setAiStatus(`Dr. Maya commented on: ${QUESTION_LABELS[questionRef] ?? 'current question'}`)
    } catch {
      setAiStatus('AI comment unavailable — check console')
    }
  }, [])

  // ── Typeform callbacks ────────────────────────────────────────────────────
  const handleQuestionChanged = useCallback(
    ({ ref }: { ref: string }) => {
      setCurrentRef(ref)
      injectComment(ref)
    },
    [injectComment]
  )

  const handleSubmit = useCallback(() => {
    setCompleted(true)
    setCurrentRef('default_tys')
    injectComment('default_tys')
  }, [injectComment])

  // ── Progress indicator ────────────────────────────────────────────────────
  const questionIndex = QUESTION_ORDER.indexOf(currentRef)
  const progress = completed
    ? 100
    : questionIndex < 0
    ? 0
    : Math.round(((questionIndex + 1) / QUESTION_ORDER.length) * 100)

  return (
    <div style={styles.root}>
      {/* ── Left panel: Typeform ─────────────────────────────────────── */}
      <div style={styles.leftPanel}>
        {/* Progress bar */}
        <div style={styles.progressBar}>
          <div style={{ ...styles.progressFill, width: `${progress}%` }} />
        </div>

        {/* Current question label */}
        <div style={styles.questionTag}>
          {completed
            ? 'Assessment complete'
            : `Section: ${QUESTION_LABELS[currentRef] ?? 'Loading…'}`}
        </div>

        {/* Typeform embedded widget */}
        <div style={styles.formWrapper}>
          <Widget
            id="ENn7cxWK"
            style={{ width: '100%', height: '100%' }}
            onQuestionChanged={handleQuestionChanged}
            onSubmit={handleSubmit}
            hideFooter
            hideHeaders
            opacity={0}
          />
        </div>
      </div>

      {/* ── Right panel: Tavus AI ────────────────────────────────────── */}
      <div style={styles.rightPanel}>
        <div style={styles.aiHeader}>
          <div style={styles.aiDot} />
          <span style={styles.aiTitle}>Dr. Maya — Cardio AI Advisor</span>
        </div>

        {/* Tavus video */}
        <div style={styles.videoWrapper}>
          {tavusState === 'loading' && (
            <div style={styles.placeholder}>
              <div style={styles.spinner} />
              <p style={styles.placeholderText}>Starting AI advisor…</p>
            </div>
          )}
          {tavusState === 'error' && (
            <div style={styles.placeholder}>
              <p style={styles.errorText}>
                Could not start Dr. Maya.
                <br />
                Check that <code>TAVUS_API_KEY</code> and <code>TAVUS_REPLICA_ID</code>{' '}
                are set in your environment.
              </p>
            </div>
          )}
          {tavusState === 'ready' && conversationUrl && (
            <iframe
              src={conversationUrl}
              allow="camera; microphone; autoplay; display-capture"
              style={styles.videoFrame}
              title="Dr. Maya – Tavus AI Advisor"
            />
          )}
        </div>

        {/* AI status strip */}
        <div style={styles.aiStatus}>
          <span style={styles.aiStatusDot}>●</span>
          {aiStatus}
        </div>

        {/* Instruction card */}
        <div style={styles.instructionCard}>
          <strong>How it works</strong>
          <ul style={styles.instructionList}>
            <li>Fill in the Cardio Quiz on the left</li>
            <li>Dr. Maya will automatically comment on each question as you answer it</li>
            <li>You can also speak directly to Dr. Maya to discuss the case</li>
            <li>A cardiology referral summary will be provided at the end</li>
          </ul>
        </div>
      </div>
    </div>
  )
}

// ─── Inline styles ────────────────────────────────────────────────────────────
const GOLD = '#EFB14D'
const BLACK = '#000000'
const WHITE = '#ffffff'

const styles: Record<string, React.CSSProperties> = {
  root: {
    display: 'flex',
    height: '100%',
    width: '100%',
    overflow: 'hidden',
    fontFamily: "'Segoe UI', system-ui, sans-serif",
    backgroundColor: '#f5f7f5',
  },

  // Left panel — Typeform
  leftPanel: {
    flex: '1 1 55%',
    display: 'flex',
    flexDirection: 'column',
    position: 'relative',
    borderRight: '1px solid #e0e0e0',
    backgroundColor: WHITE,
  },
  progressBar: {
    height: 4,
    backgroundColor: '#e0e0e0',
    flexShrink: 0,
  },
  progressFill: {
    height: '100%',
    backgroundColor: GOLD,
    transition: 'width 0.4s ease',
  },
  questionTag: {
    padding: '8px 16px',
    fontSize: 12,
    fontWeight: 600,
    color: '#555',
    backgroundColor: '#fafafa',
    borderBottom: '1px solid #eee',
    flexShrink: 0,
    textTransform: 'uppercase' as const,
    letterSpacing: '0.04em',
  },
  formWrapper: {
    flex: 1,
    overflow: 'hidden',
  },

  // Right panel — Tavus AI
  rightPanel: {
    flex: '0 0 45%',
    display: 'flex',
    flexDirection: 'column',
    backgroundColor: BLACK,
    color: WHITE,
    gap: 0,
  },
  aiHeader: {
    display: 'flex',
    alignItems: 'center',
    gap: 8,
    padding: '12px 16px',
    borderBottom: '1px solid #222',
    flexShrink: 0,
  },
  aiDot: {
    width: 8,
    height: 8,
    borderRadius: '50%',
    backgroundColor: GOLD,
    boxShadow: `0 0 6px ${GOLD}`,
  },
  aiTitle: {
    fontSize: 13,
    fontWeight: 600,
    color: WHITE,
    letterSpacing: '0.02em',
  },
  videoWrapper: {
    flex: 1,
    position: 'relative' as const,
    overflow: 'hidden',
    backgroundColor: '#111',
  },
  videoFrame: {
    width: '100%',
    height: '100%',
    border: 'none',
    display: 'block',
  },
  placeholder: {
    position: 'absolute' as const,
    inset: 0,
    display: 'flex',
    flexDirection: 'column' as const,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 16,
    backgroundColor: '#111',
  },
  placeholderText: {
    color: '#aaa',
    fontSize: 14,
    margin: 0,
  },
  errorText: {
    color: '#ff6b6b',
    fontSize: 13,
    textAlign: 'center' as const,
    lineHeight: 1.6,
    padding: '0 24px',
  },
  spinner: {
    width: 36,
    height: 36,
    border: `3px solid #333`,
    borderTop: `3px solid ${GOLD}`,
    borderRadius: '50%',
    animation: 'spin 0.8s linear infinite',
  },

  // AI status strip
  aiStatus: {
    padding: '8px 16px',
    fontSize: 12,
    color: '#aaa',
    borderTop: '1px solid #1a1a1a',
    display: 'flex',
    alignItems: 'center',
    gap: 6,
    flexShrink: 0,
    backgroundColor: '#0a0a0a',
    minHeight: 36,
  },
  aiStatusDot: {
    color: GOLD,
    fontSize: 8,
  },

  // Instruction card
  instructionCard: {
    margin: '12px',
    padding: '12px 14px',
    backgroundColor: '#111',
    borderRadius: 8,
    border: `1px solid #222`,
    fontSize: 12,
    color: '#ccc',
    flexShrink: 0,
  },
  instructionList: {
    margin: '8px 0 0',
    paddingLeft: 16,
    lineHeight: 1.8,
  },
}
