import type { NextConfig } from 'next'

const nextConfig: NextConfig = {
  async headers() {
    return [
      {
        source: '/(.*)',
        headers: [
          {
            // Allow Tavus and Typeform iframes
            key: 'Content-Security-Policy',
            value: [
              "default-src 'self'",
              "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://embed.typeform.com https://*.typeform.com",
              "frame-src 'self' https://*.typeform.com https://blackdoctor.typeform.com https://*.tavus.io https://*.daily.co https://tavus.daily.co wss://*.daily.co",
              "connect-src 'self' https://tavusapi.com https://*.typeform.com https://*.daily.co wss://*.daily.co",
              "img-src 'self' data: blob: https://*.typeform.com https://images.typeform.com https://public-assets.typeform.com https://blackdoctor-340b-ou62.vercel.app",
              "style-src 'self' 'unsafe-inline' https://*.typeform.com",
              "font-src 'self' https://*.typeform.com",
              "media-src 'self' blob: https://*.daily.co https://*.tavus.io",
            ].join('; '),
          },
        ],
      },
    ]
  },
}

export default nextConfig
