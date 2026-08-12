import { Link } from 'react-router-dom'
import PageHero from '../components/PageHero'
import Reveal from '../components/Reveal'

const HERO =
  'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1800&q=80'

const programs = [
  {
    name: 'Arts & Humanities',
    meta: '12 majors',
    desc: 'Literature, history, philosophy, languages, and studio arts — with a writing-intensive core.',
  },
  {
    name: 'Natural Sciences',
    meta: '10 majors',
    desc: 'Biology, chemistry, physics, geology, and environmental science on a living lakeshore lab.',
  },
  {
    name: 'Social Sciences',
    meta: '9 majors',
    desc: 'Economics, psychology, politics, anthropology, and sociology grounded in field research.',
  },
  {
    name: 'Mathematics & Computing',
    meta: '5 majors',
    desc: 'Pure and applied mathematics, computer science, and data studies with faculty mentorship.',
  },
  {
    name: 'Interdisciplinary Paths',
    meta: '6 concentrations',
    desc: 'Design your own major, or pursue coastal studies, global health, or creative entrepreneurship.',
  },
]

export default function Academics() {
  return (
    <>
      <PageHero
        title="Academics"
        subtitle="Forty-two ways to dig deep — and a curriculum that asks you to connect them."
        image={HERO}
      />

      <section className="section">
        <div className="container">
          <Reveal>
            <div className="section-head">
              <span className="section-label">Curriculum</span>
              <h2 className="section-title">Depth without isolation.</h2>
              <p className="section-lead">
                Every Northmere student completes a major, a writing sequence, and the Shoreline
                Seminar — a first-year course that pairs close reading with community inquiry.
              </p>
            </div>
          </Reveal>

          <Reveal delay={1}>
            <div className="program-list">
              {programs.map((p) => (
                <div className="program-item" key={p.name}>
                  <div>
                    <h3>{p.name}</h3>
                    <p>{p.desc}</p>
                  </div>
                  <span className="program-meta">{p.meta}</span>
                </div>
              ))}
            </div>
          </Reveal>
        </div>
      </section>

      <section className="section" style={{ paddingTop: 0 }}>
        <div className="container">
          <div className="split">
            <Reveal className="split-copy">
              <span className="section-label">Research</span>
              <h2 className="section-title">Undergraduate research from day one.</h2>
              <p className="section-lead">
                Over 60% of students join faculty research by junior year — from lake sediment cores
                to archival projects in the Meridian Library special collections.
              </p>
              <Link to="/contact" className="btn btn-outline">
                Talk to admissions
              </Link>
            </Reveal>
            <Reveal delay={1}>
              <div className="facts" style={{ border: 'none', padding: 0, gap: '1.5rem' }}>
                <div className="fact">
                  <strong>18</strong>
                  <span>Average class size</span>
                </div>
                <div className="fact">
                  <strong>100%</strong>
                  <span>Courses taught by faculty</span>
                </div>
                <div className="fact">
                  <strong>$2.4M</strong>
                  <span>Annual undergraduate research funding</span>
                </div>
                <div className="fact">
                  <strong>30+</strong>
                  <span>Study-away partners worldwide</span>
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>
    </>
  )
}
