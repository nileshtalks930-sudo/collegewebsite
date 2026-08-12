import { useState } from 'react'
import PageHero from '../components/PageHero'
import Reveal from '../components/Reveal'

const HERO =
  'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1800&q=80'

export default function Contact() {
  const [submitted, setSubmitted] = useState(false)

  function handleSubmit(e) {
    e.preventDefault()
    setSubmitted(true)
  }

  return (
    <>
      <PageHero
        title="Contact"
        subtitle="We are here to help — whether you are exploring Northmere or already part of the community."
        image={HERO}
      />

      <section className="section">
        <div className="container">
          <div className="contact-grid">
            <Reveal>
              <span className="section-label">Reach us</span>
              <h2 className="section-title">Start a conversation.</h2>
              <dl className="contact-details">
                <dt>Admissions</dt>
                <dd>
                  <a href="mailto:admissions@northmere.edu">admissions@northmere.edu</a>
                  <br />
                  <a href="tel:+15415550187">(541) 555-0187</a>
                </dd>
                <dt>Campus address</dt>
                <dd>
                  1200 Shoreline Drive
                  <br />
                  Northmere, OR 97330
                </dd>
                <dt>Visit hours</dt>
                <dd>
                  Monday–Friday, 9 a.m. – 4:30 p.m.
                  <br />
                  Saturday tours by appointment
                </dd>
              </dl>
            </Reveal>

            <Reveal delay={1}>
              {submitted ? (
                <div className="form-success" role="status">
                  Thank you — your message is on its way. An admissions counselor will reply within
                  two business days.
                </div>
              ) : (
                <form className="contact-form" onSubmit={handleSubmit}>
                  <div className="form-row">
                    <div className="field">
                      <label htmlFor="first">First name</label>
                      <input id="first" name="first" required autoComplete="given-name" />
                    </div>
                    <div className="field">
                      <label htmlFor="last">Last name</label>
                      <input id="last" name="last" required autoComplete="family-name" />
                    </div>
                  </div>
                  <div className="field">
                    <label htmlFor="email">Email</label>
                    <input id="email" name="email" type="email" required autoComplete="email" />
                  </div>
                  <div className="field">
                    <label htmlFor="interest">I am interested in</label>
                    <select id="interest" name="interest" defaultValue="undergrad">
                      <option value="undergrad">Undergraduate admission</option>
                      <option value="visit">Campus visit</option>
                      <option value="aid">Financial aid</option>
                      <option value="other">Something else</option>
                    </select>
                  </div>
                  <div className="field">
                    <label htmlFor="message">Message</label>
                    <textarea id="message" name="message" required placeholder="How can we help?" />
                  </div>
                  <button type="submit" className="btn btn-primary">
                    Send message
                  </button>
                </form>
              )}
            </Reveal>
          </div>
        </div>
      </section>
    </>
  )
}
