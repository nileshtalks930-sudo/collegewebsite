import { useEffect, useState } from 'react'
import { Link, NavLink, useLocation } from 'react-router-dom'

const links = [
  { to: '/about', label: 'About' },
  { to: '/academics', label: 'Academics' },
  { to: '/admissions', label: 'Admissions' },
  { to: '/campus', label: 'Campus Life' },
  { to: '/contact', label: 'Contact' },
]

function BrandMark({ className }) {
  return (
    <svg className={className} viewBox="0 0 40 40" fill="none" aria-hidden="true">
      <path
        d="M20 4L34 12.5V27.5L20 36L6 27.5V12.5L20 4Z"
        stroke="currentColor"
        strokeWidth="2"
      />
      <path d="M20 12L26 15.5V22.5L20 26L14 22.5V15.5L20 12Z" fill="currentColor" opacity="0.55" />
    </svg>
  )
}

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false)
  const [open, setOpen] = useState(false)
  const location = useLocation()
  const isHome = location.pathname === '/'

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40)
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  useEffect(() => {
    setOpen(false)
  }, [location.pathname])

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [open])

  const navClass = [
    'nav',
    scrolled || open ? 'scrolled' : '',
    !isHome && !scrolled && !open ? 'solid' : '',
    open ? 'open' : '',
  ]
    .filter(Boolean)
    .join(' ')

  return (
    <header className={navClass}>
      <div className="container nav-inner">
        <Link to="/" className="nav-brand" aria-label="Northmere College home">
          <BrandMark className="nav-mark" />
          <span className="nav-name">Northmere</span>
        </Link>

        <button
          type="button"
          className="nav-toggle"
          aria-label={open ? 'Close menu' : 'Open menu'}
          aria-expanded={open}
          onClick={() => setOpen((v) => !v)}
        >
          <span />
          <span />
          <span />
        </button>

        <ul className="nav-links">
          {links.map((link) => (
            <li key={link.to}>
              <NavLink to={link.to} className={({ isActive }) => (isActive ? 'active' : undefined)}>
                {link.label}
              </NavLink>
            </li>
          ))}
          <li>
            <Link to="/admissions" className="nav-cta">
              Apply
            </Link>
          </li>
        </ul>
      </div>
    </header>
  )
}
