import { Link } from 'react-router-dom'
import Reveal from '../components/Reveal'

const HERO =
  'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=2000&q=80'
const LIBRARY =
  'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=1200&q=80'
const QUAD =
  'https://images.unsplash.com/photo-1541339906-5a2fbd4b6b4c?auto=format&fit=crop&w=1200&q=80'

export default function Home() {
  return (
    <>
      <section className="home-hero">
        <div className="home-hero-media">
          <img
            src={HERO}
            alt="Northmere College campus quad at dusk"
            width={2000}
            height={1333}
          />
        </div>
        <div className="home-hero-content">
          <h1 className="home-brand">Northmere</h1>
          <p className="home-headline">Where the shore meets the mind.</p>
          <p className="home-support">
            A liberal arts college of 1,800 students learning beside the lake — rigorous, rooted, and
            ready for what comes next.
          </p>
          <div className="home-ctas">
            <Link to="/admissions" className="btn btn-light">
              Start your application
            </Link>
            <Link to="/about" className="btn btn-ghost">
              Discover Northmere
            </Link>
          </div>
        </div>
        <span className="home-scroll">Scroll</span>
      </section>

      <section className="section">
        <div className="container">
          <Reveal>
            <div className="facts">
              <div className="fact">
                <strong>12:1</strong>
                <span>Student–faculty ratio</span>
              </div>
              <div className="fact">
                <strong>42</strong>
                <span>Majors &amp; concentrations</span>
              </div>
              <div className="fact">
                <strong>94%</strong>
                <span>Employed or in graduate study within a year</span>
              </div>
              <div className="fact">
                <strong>$0</strong>
                <span>Loans for families under $75k</span>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="section" style={{ paddingTop: 0 }}>
        <div className="container">
          <div className="split">
            <Reveal className="split-copy">
              <span className="section-label">Academics</span>
              <h2 className="section-title">Study with people who know your name.</h2>
              <p className="section-lead">
                Seminars, studios, and field labs — not lecture halls that swallow you whole. At
                Northmere, scholarship is personal and deeply collaborative.
              </p>
              <Link to="/academics" className="btn btn-outline">
                Explore programs
              </Link>
            </Reveal>
            <Reveal delay={1} className="split-media">
              <img src={LIBRARY} alt="Students studying in the college library" />
            </Reveal>
          </div>
        </div>
      </section>

      <section className="section" style={{ paddingTop: 0 }}>
        <div className="container">
          <div className="split">
            <Reveal className="split-media">
              <img src={QUAD} alt="Historic brick academic building on campus" />
            </Reveal>
            <Reveal delay={1} className="split-copy">
              <span className="section-label">Campus</span>
              <h2 className="section-title">A lakeside campus built for belonging.</h2>
              <p className="section-lead">
                From dawn crews on the water to late nights in the makerspace, Northmere is a place
                you inhabit — not merely attend.
              </p>
              <Link to="/campus" className="btn btn-outline">
                Life on campus
              </Link>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="band">
        <div className="container">
          <Reveal>
            <div className="band-inner">
              <div>
                <h2>Applications for Fall 2027 are open.</h2>
                <p>
                  Need-blind and committed to meeting 100% of demonstrated need. Your curiosity is
                  the only prerequisite.
                </p>
              </div>
              <Link to="/admissions" className="btn btn-light">
                How to apply
              </Link>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  )
}
