import { Link } from 'react-router-dom'
import PageHero from '../components/PageHero'
import Reveal from '../components/Reveal'

const HERO =
  'https://images.unsplash.com/photo-1523580846011-d95a95ac5ce9?auto=format&fit=crop&w=1800&q=80'
const IMG_1 =
  'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=1000&q=80'
const IMG_2 =
  'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80'
const IMG_3 =
  'https://images.unsplash.com/photo-1461896836934-ffe607ba6851?auto=format&fit=crop&w=800&q=80'

export default function Campus() {
  return (
    <>
      <PageHero
        title="Campus Life"
        subtitle="Residence halls on the water, a student union that never sleeps, and 90+ clubs waiting for your energy."
        image={HERO}
      />

      <section className="section">
        <div className="container">
          <Reveal>
            <div className="section-head">
              <span className="section-label">Belong here</span>
              <h2 className="section-title">A week in the life on the shore.</h2>
              <p className="section-lead">
                Northmere is residential by design. Four years of shared meals, late-night debates,
                and sunrise on the dock become the spine of your education.
              </p>
            </div>
          </Reveal>

          <Reveal delay={1}>
            <div className="mosaic">
              <figure>
                <img src={IMG_1} alt="Students gathering outdoors on campus" />
                <figcaption>Commons lawn</figcaption>
              </figure>
              <figure>
                <img src={IMG_2} alt="Friends laughing together" />
                <figcaption>Student clubs</figcaption>
              </figure>
              <figure>
                <img src={IMG_3} alt="Athletes competing on the field" />
                <figcaption>Northmere athletics</figcaption>
              </figure>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="section" style={{ paddingTop: 0 }}>
        <div className="container">
          <Reveal>
            <div className="program-list">
              <div className="program-item">
                <div>
                  <h3>Housing &amp; dining</h3>
                  <p>
                    First-years live in lakeside halls with faculty mentors nearby. All-you-care-to-eat
                    dining emphasizes local Oregon farms and dietary flexibility.
                  </p>
                </div>
                <span className="program-meta">9 halls</span>
              </div>
              <div className="program-item">
                <div>
                  <h3>Clubs &amp; organizations</h3>
                  <p>
                    From a cappella to robotics, outdoor leadership to student government — if it
                    does not exist yet, you can start it with a small grant.
                  </p>
                </div>
                <span className="program-meta">90+ groups</span>
              </div>
              <div className="program-item">
                <div>
                  <h3>Athletics &amp; recreation</h3>
                  <p>
                    Compete in NCAA Division III, paddle at dawn, or climb indoors. Wellness is part
                    of the curriculum of living well.
                  </p>
                </div>
                <span className="program-meta">18 varsity teams</span>
              </div>
              <div className="program-item">
                <div>
                  <h3>Arts on campus</h3>
                  <p>
                    The Meridian Theater, student galleries, and an open makerspace keep creative
                    practice at the center of campus culture.
                  </p>
                </div>
                <span className="program-meta">Year-round</span>
              </div>
            </div>
          </Reveal>

          <Reveal>
            <div style={{ marginTop: '2.5rem' }}>
              <Link to="/admissions" className="btn btn-outline">
                Plan a campus visit
              </Link>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  )
}
