import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'Cardio Quiz — BlackDoctor.org',
  description: 'Cardiac risk screening with AI-powered clinical commentary for African American patients.',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body style={{ height: '100%', margin: 0, display: 'flex', flexDirection: 'column', fontFamily: "'Segoe UI', system-ui, sans-serif", backgroundColor: '#000' }}>
        {/* ── Header ───────────────────────────────────────────────── */}
        <header style={{
          background: '#000000',
          color: '#fff',
          padding: '14px 24px',
          display: 'flex',
          alignItems: 'center',
          gap: 12,
          flexShrink: 0,
          borderBottom: '1px solid #1a1a1a',
        }}>
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src="https://blackdoctor-340b-ou62.vercel.app/BlackDoctor_PrimaryLogo_Black.svg"
            alt="BlackDoctor.org"
            style={{ height: 28, width: 'auto', filter: 'invert(1)' }}
          />
          <div style={{ height: 20, width: 1, background: '#333' }} />
          <span style={{ fontSize: 13, fontWeight: 600, color: '#EFB14D', letterSpacing: '0.04em' }}>
            CARDIO QUIZ
          </span>
          <span style={{ fontSize: 12, color: '#777', marginLeft: 'auto' }}>
            AI-Assisted Cardiac Risk Screening
          </span>
        </header>

        {/* ── Main content ─────────────────────────────────────────── */}
        <main style={{ flex: 1, height: 0, overflow: 'hidden', display: 'flex' }}>
          {children}
        </main>

        {/* ── Footer ───────────────────────────────────────────────── */}
        <footer style={{
          textAlign: 'center',
          padding: '10px 16px',
          fontSize: 11,
          color: '#555',
          backgroundColor: '#000',
          borderTop: '1px solid #1a1a1a',
          flexShrink: 0,
        }}>
          © 2026 BlackDoctor.org — Cardio Quiz &nbsp;|&nbsp; For clinical screening purposes only. Not a substitute for professional medical judgment.
        </footer>
      </body>
    </html>
  )
}
