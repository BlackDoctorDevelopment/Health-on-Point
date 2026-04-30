'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import { useParams } from 'next/navigation'

export default function ResultsPage() {
  const router = useRouter()
  const params = useParams()
  const sessionId = params?.sessionId as string
  const [downloading, setDownloading] = useState(false)

  const handleRestart = () => {
    router.push('/')
  }

  const handleDownload = async () => {
    setDownloading(true)
    try {
      const res = await fetch(`/api/pdf/${sessionId}`)
      if (!res.ok) throw new Error('PDF generation failed')
      const blob = await res.blob()
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `cardio-assessment-${sessionId}.pdf`
      a.click()
      URL.revokeObjectURL(url)
    } catch (err) {
      console.error('Download error:', err)
    } finally {
      setDownloading(false)
    }
  }

  return (
    <div style={styles.page}>
      <header style={styles.header}>
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src="/BlackDoctor_PrimaryLogo_Black.png"
          alt="BlackDoctor.org"
          style={styles.logo}
        />
        <div style={{ width: 1, height: 20, backgroundColor: '#e0e0e0' }} />
        <span style={styles.headerSub}>Cardio Quiz</span>
        <div style={styles.headerSpacer} />
        <button style={styles.headerRestartBtn} onClick={handleRestart}>
          ↺ New Session
        </button>
      </header>

      <main style={styles.main}>
        {/* ── Score / Result Card ─────────────────────────────────── */}
        <div style={styles.card}>
          <h1 style={styles.greeting}>Assessment Complete</h1>
          <p style={styles.subline}>
            Thank you for completing the BlackDoctor.org Cardio Risk Assessment.
            Dr. Mitchell has reviewed your responses below.
          </p>

          {/* Action buttons — above the fold */}
          <div style={styles.actionButtons}>
            <a
              href="https://blackdoctor.com/find-a-doctor/"
              target="_blank"
              rel="noopener noreferrer"
              style={styles.findDoctorBtn}
            >
              Find a Cardiologist Near You →
            </a>
            <a
              href="https://blackdoctor.com/heart-health/"
              target="_blank"
              rel="noopener noreferrer"
              style={styles.learnMoreBtn}
            >
              Heart Health Resources
            </a>
            <button
              style={styles.downloadBtn}
              onClick={handleDownload}
              disabled={downloading}
            >
              {downloading ? 'Preparing…' : '⬇ Download PDF'}
            </button>
            <button style={styles.restartBtn} onClick={handleRestart}>
              ↺ New Session
            </button>
          </div>

          <p style={styles.sessionNote}>
            Session ID: <code style={styles.sessionCode}>{sessionId}</code>
          </p>
        </div>

        {/* ── Clinical context card ───────────────────────────────── */}
        <div style={styles.card}>
          <h2 style={styles.sectionTitle}>Why This Assessment Matters</h2>
          <p style={styles.bodyText}>
            African American patients have a higher prevalence of hypertension, heart failure,
            and stroke — often with earlier onset and greater severity compared to other
            populations. A lower threshold for cardiology referral is appropriate when
            multiple risk factors are present.
          </p>
          <p style={styles.bodyText}>
            If you answered <strong>Yes</strong> to two or more questions in this assessment,
            please discuss a cardiology referral with your physician.
          </p>
        </div>

        {/* ── Resources card ─────────────────────────────────────── */}
        <div style={styles.card}>
          <h2 style={styles.sectionTitle}>Next Steps</h2>
          <ul style={styles.recList}>
            <li style={styles.recItem}>
              <span style={styles.recBullet}>→</span>
              <span>
                <strong>Find a cardiologist</strong> — Use the BlackDoctor.org doctor finder
                to locate a cardiologist near you.{' '}
                <a href="https://blackdoctor.com/find-a-doctor/" target="_blank" rel="noopener noreferrer" style={styles.link}>
                  Find a Doctor
                </a>
              </span>
            </li>
            <li style={styles.recItem}>
              <span style={styles.recBullet}>→</span>
              <span>
                <strong>Learn more about heart health</strong> — Read articles, watch videos,
                and find resources on heart health for African Americans.{' '}
                <a href="https://blackdoctor.com/heart-health/" target="_blank" rel="noopener noreferrer" style={styles.link}>
                  Heart Health →
                </a>
              </span>
            </li>
            <li style={styles.recItem}>
              <span style={styles.recBullet}>→</span>
              <span>
                <strong>Talk to your doctor</strong> — Share the results of this assessment
                with your primary care physician and ask about your cardiovascular risk.
              </span>
            </li>
          </ul>
        </div>
      </main>

      <footer style={styles.footer}>
        <p>© {new Date().getFullYear()} BlackDoctor.org — Cardio Quiz</p>
        <p style={styles.footerDisclaimer}>
          For clinical screening purposes only. Not a substitute for professional medical judgment.
        </p>
      </footer>
    </div>
  )
}

const GOLD = '#EFB14D'

const styles: Record<string, React.CSSProperties> = {
  page: {
    minHeight: '100vh',
    backgroundColor: '#f5f7f5',
    fontFamily: "'Segoe UI', system-ui, sans-serif",
    display: 'flex',
    flexDirection: 'column',
  },
  header: {
    backgroundColor: '#000',
    borderBottom: '1px solid #1a1a1a',
    padding: '14px 24px',
    display: 'flex',
    alignItems: 'center',
    gap: 12,
    flexShrink: 0,
  },
  headerSpacer: {
    flex: 1,
  },
  headerRestartBtn: {
    backgroundColor: GOLD,
    color: '#000',
    border: 'none',
    borderRadius: 8,
    padding: '8px 16px',
    fontSize: 13,
    fontWeight: 600,
    cursor: 'pointer',
    flexShrink: 0,
  },
  logo: {
    height: 28,
    width: 'auto',
    filter: 'invert(1)',
  },
  headerSub: {
    fontSize: 13,
    fontWeight: 600,
    color: GOLD,
    letterSpacing: '0.04em',
  },
  main: {
    flex: 1,
    maxWidth: 720,
    margin: '0 auto',
    padding: '32px 24px',
    width: '100%',
    boxSizing: 'border-box' as const,
  },
  card: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: '28px 32px',
    marginBottom: 24,
    boxShadow: '0 2px 8px rgba(0,0,0,0.07)',
  },
  greeting: {
    fontSize: 26,
    fontWeight: 700,
    color: '#1a1a1a',
    marginTop: 0,
    marginBottom: 8,
  },
  subline: {
    fontSize: 15,
    color: '#555',
    marginBottom: 24,
    lineHeight: 1.6,
  },
  actionButtons: {
    display: 'flex',
    flexDirection: 'row' as const,
    flexWrap: 'wrap' as const,
    gap: 12,
    marginBottom: 20,
  },
  findDoctorBtn: {
    display: 'inline-block',
    backgroundColor: '#1a1a1a',
    color: '#fff',
    padding: '12px 20px',
    borderRadius: 8,
    textDecoration: 'none',
    fontWeight: 600,
    fontSize: 14,
    textAlign: 'center' as const,
  },
  learnMoreBtn: {
    display: 'inline-block',
    backgroundColor: '#f0f0f0',
    color: '#1a1a1a',
    padding: '12px 20px',
    borderRadius: 8,
    textDecoration: 'none',
    fontWeight: 600,
    fontSize: 14,
    textAlign: 'center' as const,
  },
  downloadBtn: {
    backgroundColor: '#1a1a1a',
    color: '#fff',
    border: 'none',
    borderRadius: 8,
    padding: '12px 20px',
    fontSize: 14,
    fontWeight: 600,
    cursor: 'pointer',
  },
  restartBtn: {
    backgroundColor: GOLD,
    color: '#000',
    border: 'none',
    borderRadius: 8,
    padding: '12px 20px',
    fontSize: 14,
    fontWeight: 600,
    cursor: 'pointer',
  },
  sessionNote: {
    fontSize: 11,
    color: '#aaa',
    margin: 0,
  },
  sessionCode: {
    fontSize: 11,
    color: '#999',
    backgroundColor: '#f5f5f5',
    padding: '2px 6px',
    borderRadius: 4,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 700,
    color: '#1a1a1a',
    marginTop: 0,
    marginBottom: 12,
  },
  bodyText: {
    fontSize: 15,
    color: '#444',
    lineHeight: 1.7,
    marginBottom: 12,
  },
  recList: {
    listStyle: 'none',
    padding: 0,
    margin: 0,
  },
  recItem: {
    padding: '10px 0',
    borderBottom: '1px solid #f0f0f0',
    fontSize: 15,
    color: '#333',
    display: 'flex',
    alignItems: 'flex-start',
    gap: 10,
    lineHeight: 1.5,
  },
  recBullet: {
    color: GOLD,
    fontWeight: 700,
    flexShrink: 0,
    marginTop: 2,
  },
  link: {
    color: '#1a1a1a',
    fontWeight: 600,
  },
  footer: {
    backgroundColor: '#000',
    borderTop: '1px solid #1a1a1a',
    padding: '14px 24px',
    textAlign: 'center' as const,
    fontSize: 12,
    color: '#555',
    flexShrink: 0,
  },
  footerDisclaimer: {
    fontSize: 11,
    color: '#444',
    marginTop: 4,
  },
}
