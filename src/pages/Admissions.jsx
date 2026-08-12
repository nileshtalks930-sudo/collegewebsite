import { Link } from 'react-router-dom'
import PageHero from '../components/PageHero'
import Reveal from '../components/Reveal'

const HERO =
  'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1800&q=80'

export default function Admissions() {
  return (
    <>
      <PageHero
        title="Admissions"
        subtitle="We read every application carefully. Bring us your curiosity — we will meet you where you are."
        image={HERO}
      />

      <section className="section">
        <div className="container">
          <Reveal>
            <div className="section-head">
              <span className="section-label">How to apply</span>
              <h2 className="section-title">Three clear steps.</h2>
            </div>
          </Reveal>
          <Reveal delay={1}>
            <div className="steps">
              <div className="step">
                <h3>Choose your path</h3>
                <p>
                  Apply via the Common Application or Coalition. Early Decision, Early Action, and
                  Regular Decision are all available.
                </p>
              </div>
              <div className="step">
                <h3>Tell your story</h3>
                <p>
                  Submit transcripts, one counselor recommendation, and two teacher recommendations.
                  Test scores are optional.
                </p>
              </div>
              <div className="step">
                <h3>Complete aid forms</h3>
                <p>
                  File the FAFSA and CSS Profile by the priority deadline to be considered for
                  Northmere Grant aid.
                </p>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="section" style={{ paddingTop: 0 }}>
        <div className="container">
          <Reveal>
            <div className="section-head">
              <span className="section-label">Key dates</span>
              <h2 className="section-title">Fall 2027 deadlines.</h2>
            </div>
          </Reveal>
          <Reveal delay={1}>
            <table className="deadlines">
              <thead>
                <tr>
                  <th>Plan</th>
                  <th>Deadline</th>
                  <th>Notification</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Early Decision</td>
                  <td>November 15</td>
                  <td>December 15</td>
                </tr>
                <tr>
                  <td>Early Action</td>
                  <td>November 15</td>
                  <td>January 20</td>
                </tr>
                <tr>
                  <td>Regular Decision</td>
                  <td>January 15</td>
                  <td>April 1</td>
                </tr>
                <tr>
                  <td>Financial aid priority</td>
                  <td>February 1</td>
                  <td>With admission</td>
                </tr>
              </tbody>
            </table>
          </Reveal>
        </div>
      </section>

      <section className="band">
        <div className="container">
          <Reveal>
            <div className="band-inner">
              <div>
                <h2>Ready when you are.</h2>
                <p>
                  Questions about fit, aid, or visiting campus? Our counselors reply within two
                  business days.
                </p>
              </div>
              <div style={{ display: 'flex', gap: '0.85rem', flexWrap: 'wrap' }}>
                <Link to="/contact" className="btn btn-light">
                  Request information
                </Link>
                <a
                  className="btn btn-ghost"
                  href="https://www.commonapp.org/"
                  target="_blank"
                  rel="noreferrer"
                >
                  Common App
                </a>
              </div>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  )
}
