export default function PageHero({ title, subtitle, image }) {
  return (
    <section className="page-hero">
      <div className="page-hero-bg" style={{ backgroundImage: `url(${image})` }} />
      <div className="container">
        <h1>{title}</h1>
        {subtitle ? <p>{subtitle}</p> : null}
      </div>
    </section>
  )
}
