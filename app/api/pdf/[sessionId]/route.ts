import { NextRequest, NextResponse } from 'next/server'
// @ts-expect-error — pdfkit ships CJS; types come from @types/pdfkit
import PDFDocument from 'pdfkit'

const GOLD = '#EFB14D'
const BLACK = '#1a1a1a'
const GRAY = '#555555'
const LIGHT_GRAY = '#888888'

export async function GET(
  _req: NextRequest,
  { params }: { params: Promise<{ sessionId: string }> }
) {
  const { sessionId } = await params

  // Build the PDF in memory via a stream → Buffer
  const chunks: Buffer[] = []
  await new Promise<void>((resolve, reject) => {
    const doc = new PDFDocument({ size: 'LETTER', margin: 56, info: {
      Title: 'BlackDoctor.org — Cardio Risk Assessment',
      Author: 'BlackDoctor.org Health On Point',
      Subject: 'Cardio Risk Assessment Report',
    }})

    doc.on('data', (chunk: Buffer) => chunks.push(chunk))
    doc.on('end', resolve)
    doc.on('error', reject)

    const W = doc.page.width   // 612
    const leftX = 56
    const rightX = W - 56

    // ── Gold accent bar ──────────────────────────────────────────────
    doc.rect(0, 0, W, 8).fill(GOLD)

    // ── Header ──────────────────────────────────────────────────────
    doc.moveDown(0.5)
    doc
      .font('Helvetica-Bold')
      .fontSize(22)
      .fillColor(BLACK)
      .text('BlackDoctor.org', leftX, 28)

    doc
      .font('Helvetica')
      .fontSize(11)
      .fillColor(GOLD)
      .text('Health On Point  ·  Cardio Quiz', leftX, 56)

    // Horizontal rule
    doc.moveTo(leftX, 76).lineTo(rightX, 76).strokeColor(GOLD).lineWidth(1.5).stroke()

    // ── Title ────────────────────────────────────────────────────────
    doc
      .font('Helvetica-Bold')
      .fontSize(18)
      .fillColor(BLACK)
      .text('Cardio Risk Assessment Report', leftX, 90)

    doc
      .font('Helvetica')
      .fontSize(10)
      .fillColor(LIGHT_GRAY)
      .text(`Session ID: ${sessionId}`, leftX, 116)
      .text(`Generated: ${new Date().toLocaleDateString('en-US', { dateStyle: 'long' })}`, leftX, 130)

    doc.moveTo(leftX, 150).lineTo(rightX, 150).strokeColor('#e0e0e0').lineWidth(0.5).stroke()

    // ── About section ───────────────────────────────────────────────
    let y = 164

    doc.font('Helvetica-Bold').fontSize(13).fillColor(BLACK).text('About This Assessment', leftX, y)
    y += 20

    doc.font('Helvetica').fontSize(10.5).fillColor(GRAY)
      .text(
        'This assessment was developed to help identify cardiovascular risk factors with particular relevance to African American patients, who experience higher rates of hypertension, heart failure, and stroke — often with earlier onset and greater severity compared to other populations.',
        leftX, y, { width: rightX - leftX, lineGap: 3 }
      )
    y = doc.y + 16

    doc.font('Helvetica').fontSize(10.5).fillColor(GRAY)
      .text(
        'If you answered Yes to two or more questions in this assessment, please discuss a cardiology referral with your physician.',
        leftX, y, { width: rightX - leftX, lineGap: 3 }
      )
    y = doc.y + 20

    doc.moveTo(leftX, y).lineTo(rightX, y).strokeColor('#e0e0e0').lineWidth(0.5).stroke()
    y += 14

    // ── Questions covered ────────────────────────────────────────────
    doc.font('Helvetica-Bold').fontSize(13).fillColor(BLACK).text('Questions Covered', leftX, y)
    y += 18

    const questions = [
      'Do you experience chest pain, pressure, or discomfort — especially with exertion?',
      'Do you have hypertension, diabetes, high cholesterol, or chronic kidney disease?',
      'Do you experience shortness of breath, reduced exercise tolerance, dizziness, or fainting?',
      'Do you have a family history of premature cardiovascular disease (before age 55)?',
      'Do you experience palpitations, lower-extremity swelling, or unexplained fatigue?',
    ]

    for (let i = 0; i < questions.length; i++) {
      // Gold bullet circle
      doc.circle(leftX + 6, y + 6, 5).fill(GOLD)
      doc
        .font('Helvetica-Bold')
        .fontSize(10)
        .fillColor('#fff')
        .text(`${i + 1}`, leftX + 3, y + 2)

      doc
        .font('Helvetica')
        .fontSize(10.5)
        .fillColor(BLACK)
        .text(questions[i], leftX + 18, y, { width: rightX - leftX - 18, lineGap: 2 })

      y = doc.y + 10
    }

    y += 4
    doc.moveTo(leftX, y).lineTo(rightX, y).strokeColor('#e0e0e0').lineWidth(0.5).stroke()
    y += 14

    // ── Next steps ───────────────────────────────────────────────────
    doc.font('Helvetica-Bold').fontSize(13).fillColor(BLACK).text('Recommended Next Steps', leftX, y)
    y += 18

    const steps = [
      ['Find a Cardiologist', 'Use the BlackDoctor.org doctor finder to locate a board-certified cardiologist near you: blackdoctor.com/find-a-doctor/'],
      ['Heart Health Resources', 'Read articles, watch videos, and access resources on heart health for African Americans: blackdoctor.com/heart-health/'],
      ['Talk to Your Doctor', 'Share these results with your primary care physician and ask about your cardiovascular risk profile and whether a cardiology referral is appropriate.'],
    ]

    for (const [title, body] of steps) {
      doc.font('Helvetica-Bold').fontSize(10.5).fillColor(BLACK).text(`→  ${title}`, leftX, y)
      y = doc.y + 2
      doc.font('Helvetica').fontSize(10).fillColor(GRAY).text(body, leftX + 14, y, { width: rightX - leftX - 14, lineGap: 2 })
      y = doc.y + 12
    }

    // ── Footer rule ──────────────────────────────────────────────────
    const footerY = doc.page.height - 56
    doc.moveTo(leftX, footerY).lineTo(rightX, footerY).strokeColor(GOLD).lineWidth(1).stroke()

    doc
      .font('Helvetica')
      .fontSize(9)
      .fillColor(LIGHT_GRAY)
      .text(
        `© ${new Date().getFullYear()} BlackDoctor.org  ·  Health On Point`,
        leftX,
        footerY + 8,
        { align: 'center', width: rightX - leftX }
      )
    doc
      .font('Helvetica')
      .fontSize(8)
      .fillColor(LIGHT_GRAY)
      .text(
        'For informational and clinical screening purposes only. Not a substitute for professional medical judgment.',
        leftX,
        footerY + 20,
        { align: 'center', width: rightX - leftX }
      )

    doc.end()
  })

  const pdf = Buffer.concat(chunks)

  return new NextResponse(pdf, {
    status: 200,
    headers: {
      'Content-Type': 'application/pdf',
      'Content-Disposition': `attachment; filename="cardio-assessment-${sessionId}.pdf"`,
      'Content-Length': String(pdf.byteLength),
    },
  })
}
