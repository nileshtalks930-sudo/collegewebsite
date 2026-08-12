import PageHero from '../components/PageHero'
import Reveal from '../components/Reveal'

const HERO =
  'https://images.unsplash.com/photo-1607237138185-eedd9c632b0b?auto=format&fit=crop&w=1800&q=80'
const PORTRAIT_1 =
  'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=800&q=80'
const PORTRAIT_2 =
  'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&q=80'
const PORTRAIT_3 =
  'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=800&q=80'

export default function About() {
  return (
    <>
      <PageHero
        title="About Northmere"
        subtitle="Founded on the northern shore in 1887 — still guided by the same idea: education as a public good and a private calling."
        image={HERO}
      />

      <section className="section">
        <div className="container">
          <div className="split">
            <Reveal>
              <span className="section-label">Our mission</span>
              <h2 className="section-title">Curiosity with consequence.</h2>
            </Reveal>
            <Reveal delay={1} className="prose">
              <p>
                Northmere College prepares students to ask better questions — and to act on the
                answers. We believe a liberal arts education is not a luxury; it is the foundation
                for a life of purpose, civic courage, and creative work.
              </p>
              <p>
                Nestled along 220 acres of lakeshore forest in Oregon, our campus is both sanctuary
                and launchpad. Small classes, ambitious research, and a culture of mentorship define
                how we learn together.
              </p>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="section" style={{ paddingTop: 0 }}>
        <div className="container">
          <Reveal>
            <div className="section-head">
              <span className="section-label">History</span>
              <h2 className="section-title">A college shaped by place.</h2>
            </div>
          </Reveal>
          <Reveal delay={1}>
            <div className="timeline">
              <div className="timeline-item">
                <time>1887</time>
                <h3>Founding</h3>
                <p>
                  Timber merchants and teachers charter Northmere as a coeducational college dedicated
                  to classical study and practical science.
                </p>
              </div>
              <div className="timeline-item">
                <time>1952</time>
                <h3>The lakeshore expansion</h3>
                <p>
                  New residence halls and the Shoreline Science Center open, anchoring research in
                  ecology, geology, and limnology.
                </p>
              </div>
              <div className="timeline-item">
                <time>1998</time>
                <h3>Need-blind admissions</h3>
                <p>
                  Northmere commits to need-blind undergraduate admissions and meeting 100% of
                  demonstrated financial need.
                </p>
              </div>
              <div className="timeline-item">
                <time>Today</time>
                <h3>1,800 students, one shore</h3>
                <p>
                  Students from 48 states and 40 countries study across 42 majors — still walking the
                  same lakeside paths.
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
              <span className="section-label">Leadership</span>
              <h2 className="section-title">People behind the mission.</h2>
            </div>
          </Reveal>
          <div className="people">
            <Reveal className="person">
              <img src={PORTRAIT_1} alt="President Elena Vargas" />
              <h3>Elena Vargas, Ph.D.</h3>
              <p className="role">President</p>
              <p>Historian of the American West and champion of access in higher education.</p>
            </Reveal>
            <Reveal delay={1} className="person">
              <img src={PORTRAIT_2} alt="Provost Marcus Chen" />
              <h3>Marcus Chen, Ph.D.</h3>
              <p className="role">Provost</p>
              <p>Leads academic affairs with a focus on undergraduate research and writing.</p>
            </Reveal>
            <Reveal delay={2} className="person">
              <img src={PORTRAIT_3} alt="Dean of Students Aisha Rahman" />
              <h3>Aisha Rahman</h3>
              <p className="role">Dean of Students</p>
              <p>Builds campus culture around wellness, belonging, and student voice.</p>
            </Reveal>
          </div>
        </div>
      </section>
    </>
  )
}
