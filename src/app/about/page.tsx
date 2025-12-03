export default function AboutPage() {
  const features = [
    { icon: "fa-file-lines", color: "text-blue-500", title: "Document Editor", desc: "Full-featured WYSIWYG editor with TinyMCE" },
    { icon: "fa-robot", color: "text-purple-500", title: "AI Assistant", desc: "Powered by Google Gemini for writing assistance" },
    { icon: "fa-folder-open", color: "text-green-500", title: "File Manager", desc: "Upload, organize, and manage your files" },
    { icon: "fa-file-export", color: "text-orange-500", title: "Page Formats", desc: "Support for A4, F4, Legal, and Letter sizes" },
  ];

  const techStack = [
    { icon: "fa-brands fa-react", color: "text-cyan-500", name: "Next.js", desc: "React Framework" },
    { icon: "fa-solid fa-database", color: "text-blue-500", name: "Prisma", desc: "Database ORM" },
    { icon: "fa-brands fa-css3-alt", color: "text-sky-500", name: "TailwindCSS", desc: "Styling" },
    { icon: "fa-solid fa-wand-magic-sparkles", color: "text-purple-500", name: "Gemini AI", desc: "AI Assistant" },
  ];

  const aiActions = [
    { icon: "fa-pen", color: "text-blue-500", name: "Write", desc: "Generate new content based on your prompt" },
    { icon: "fa-forward", color: "text-green-500", name: "Continue", desc: "Continue writing from where you left off" },
    { icon: "fa-wand-magic-sparkles", color: "text-purple-500", name: "Improve", desc: "Enhance grammar, structure, and clarity" },
    { icon: "fa-compress", color: "text-orange-500", name: "Summarize", desc: "Create a concise summary of the content" },
    { icon: "fa-language", color: "text-cyan-500", name: "Translate", desc: "Translate content to Indonesian" },
    { icon: "fa-expand", color: "text-pink-500", name: "Expand", desc: "Add more details and explanations" },
  ];

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center gap-4 mb-8">
        <div className="w-14 h-14 rounded-xl bg-[var(--accent)] text-white flex items-center justify-center">
          <i className="fa-solid fa-file-signature text-2xl"></i>
        </div>
        <div>
          <h1 className="text-2xl font-semibold">ElectronicSheet</h1>
          <p className="text-[var(--secondary)] mt-1">Document Management System with AI Assistant v1.0.0</p>
        </div>
      </div>

      {/* Grid Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Features */}
        <div className="card">
          <h3 className="font-semibold mb-4">Features</h3>
          <div className="space-y-4">
            {features.map((f) => (
              <div key={f.title} className="flex items-start gap-3">
                <div className={`${f.color} mt-0.5`}>
                  <i className={`fa-solid ${f.icon}`}></i>
                </div>
                <div>
                  <p className="font-medium">{f.title}</p>
                  <p className="text-sm text-[var(--secondary)]">{f.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* AI Actions */}
        <div className="card">
          <h3 className="font-semibold mb-4">AI Writing Actions</h3>
          <div className="space-y-4">
            {aiActions.map((a) => (
              <div key={a.name} className="flex items-start gap-3">
                <div className={`${a.color} mt-0.5`}>
                  <i className={`fa-solid ${a.icon}`}></i>
                </div>
                <div>
                  <p className="font-medium">{a.name}</p>
                  <p className="text-sm text-[var(--secondary)]">{a.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Tech Stack */}
        <div className="lg:col-span-2 card">
          <h3 className="font-semibold mb-4">Technology Stack</h3>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {techStack.map((t) => (
              <div key={t.name} className="flex items-center gap-3 p-4 rounded-lg bg-[var(--border)]">
                <i className={`${t.icon} ${t.color} text-2xl`}></i>
                <div>
                  <p className="font-medium">{t.name}</p>
                  <p className="text-sm text-[var(--secondary)]">{t.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Developer Info */}
        <div className="lg:col-span-2 card">
          <h3 className="font-semibold mb-4 flex items-center gap-2">
            <i className="fa-solid fa-user-gear text-[var(--accent)]"></i>
            Developer Information
          </h3>
          <div className="flex flex-col md:flex-row items-start gap-6">
            <div className="flex-1">
              <div className="flex items-center gap-4 mb-4">
                <div className="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-white text-xl font-bold">
                  R
                </div>
                <div>
                  <h4 className="text-lg font-semibold">risunCode</h4>
                  <p className="text-[var(--secondary)]">Full Stack Developer</p>
                </div>
              </div>
              
              <p className="text-[var(--secondary)] mb-4 leading-relaxed">
                Passionate developer focused on creating modern web applications with AI integration. 
                Specialized in Next.js, React, and cutting-edge technologies to deliver exceptional user experiences.
              </p>

              <div className="flex flex-wrap items-center gap-3">
                <a 
                  href="https://github.com/risunCode/ElectronicSheet" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition-colors"
                >
                  <i className="fa-brands fa-github"></i>
                  View Source Code
                </a>
                <a 
                  href="https://github.com/risunCode" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  className="flex items-center gap-2 px-4 py-2 bg-[var(--accent)] hover:bg-[var(--accent)]/80 text-white rounded-lg transition-colors"
                >
                  <i className="fa-solid fa-external-link-alt"></i>
                  GitHub Profile
                </a>
              </div>
            </div>

            <div className="w-full md:w-auto">
              <div className="bg-[var(--border)] p-4 rounded-lg">
                <h5 className="font-semibold mb-3 text-sm">Project Stats</h5>
                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-[var(--secondary)]">Version:</span>
                    <span className="font-mono">v1.0.0</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-[var(--secondary)]">License:</span>
                    <span>MIT</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-[var(--secondary)]">Framework:</span>
                    <span>Next.js 14</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-[var(--secondary)]">AI Model:</span>
                    <span>Gemini 2.5</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
