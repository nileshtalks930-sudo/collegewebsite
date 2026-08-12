import { Link } from 'react-router-dom'

export default function Footer() {
  return (
    <footer className="footer">
      <div className="container">
        <div className="footer-grid">
          <div>
            <div className="footer-brand">Northmere College</div>
            <p className="footer-blurb">
              A liberal arts college on the northern shore, where curiosity meets craft since 1887.
            </p>
          </div>
          <div>
            <h4>Explore</h4>
            <ul>
              <li>
                <Link to="/about">About</Link>
              </li>
              <li>
                <Link to="/academics">Academics</Link>
              </li>
              <li>
                <Link to="/campus">Campus Life</Link>
              </li>
            </ul>
          </div>
          <div>
            <h4>Apply</h4>
            <ul>
              <li>
                <Link to="/admissions">Undergraduate</Link>
              </li>
              <li>
                <Link to="/admissions">Visit Campus</Link>
              </li>
              <li>
                <Link to="/contact">Request Info</Link>
              </li>
            </ul>
          </div>
          <div>
            <h4>Visit</h4>
            <ul>
              <li>1200 Shoreline Drive</li>
              <li>Northmere, OR 97330</li>
              <li>
                <a href="tel:+15415550187">(541) 555-0187</a>
              </li>
            </ul>
          </div>
        </div>
        <div className="footer-bottom">
          <span>© {new Date().getFullYear()} Northmere College</span>
          <span>Equal opportunity · Need-blind admissions</span>
        </div>
      </div>
    </footer>
  )
}
