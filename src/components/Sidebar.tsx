"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState, useEffect } from "react";

const navSections = [
  {
    title: "Menu",
    items: [
      { href: "/", label: "Dashboard", icon: "fa-solid fa-house" },
      { href: "/documents", label: "Documents", icon: "fa-solid fa-file-lines" },
      { href: "/files", label: "Files", icon: "fa-solid fa-folder-open" },
    ],
  },
  {
    title: "System",
    items: [
      { href: "/settings", label: "Settings", icon: "fa-solid fa-gear" },
      { href: "/about", label: "About", icon: "fa-solid fa-circle-info" },
    ],
  },
];

export default function Sidebar() {
  const pathname = usePathname();
  const [isPinned, setIsPinned] = useState(true);
  const [isHovered, setIsHovered] = useState(false);

  // Load preferences from localStorage
  useEffect(() => {
    const savedPinned = localStorage.getItem("sidebar_pinned");
    
    if (savedPinned !== null) {
      const pinned = savedPinned === "true";
      setIsPinned(pinned);
      if (!pinned) {
        document.body.classList.add("sidebar-collapsed");
      }
    }
  }, []);

  const togglePin = () => {
    const newValue = !isPinned;
    setIsPinned(newValue);
    localStorage.setItem("sidebar_pinned", String(newValue));
    if (newValue) {
      document.body.classList.remove("sidebar-collapsed");
    } else {
      document.body.classList.add("sidebar-collapsed");
    }
  };

  const isExpanded = isPinned || isHovered;

  return (
    <aside
      className={`
        fixed left-0 top-0 h-screen z-40 flex flex-col
        bg-[var(--card)] border-r border-[var(--border)]
        transition-all duration-300 ease-in-out
        ${isExpanded ? "w-52" : "w-14"}
      `}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* Logo */}
      <div className="h-12 flex items-center px-3 border-b border-[var(--border)]">
        <Link href="/" className="flex items-center gap-2 overflow-hidden">
          <i className="fa-solid fa-file-signature text-lg text-[var(--accent)] flex-shrink-0"></i>
          <span className={`font-semibold text-sm whitespace-nowrap transition-opacity duration-300 ${isExpanded ? "opacity-100" : "opacity-0"}`}>
            ElectronicSheet
          </span>
        </Link>
      </div>

      {/* Navigation */}
      <nav className="flex-1 py-2 overflow-y-auto">
        {navSections.map((section, sectionIndex) => (
          <div key={section.title} className={sectionIndex > 0 ? "mt-4" : ""}>
            {isExpanded && (
              <div className="sidebar-section-title">{section.title}</div>
            )}
            <ul className="space-y-0.5 px-2">
              {section.items.map((item) => {
                const isActive = pathname === item.href || 
                  (item.href !== "/" && pathname.startsWith(item.href));
                
                return (
                  <li key={item.href}>
                    <Link
                      href={item.href}
                      className={`
                        flex items-center gap-2.5 px-2.5 py-2 rounded-md transition-colors text-sm
                        ${isActive 
                          ? "bg-[var(--border)] text-[var(--foreground)] font-medium" 
                          : "text-[var(--secondary)] hover:bg-[var(--border)] hover:text-[var(--foreground)]"
                        }
                      `}
                      title={!isExpanded ? item.label : undefined}
                    >
                      <i className={`${item.icon} w-4 text-center flex-shrink-0 text-xs`}></i>
                      <span className={`whitespace-nowrap transition-opacity duration-300 ${isExpanded ? "opacity-100" : "opacity-0"}`}>
                        {item.label}
                      </span>
                    </Link>
                  </li>
                );
              })}
            </ul>
          </div>
        ))}
      </nav>

      {/* Pin Toggle */}
      <div className="border-t border-[var(--border)] p-2">
        <button
          onClick={togglePin}
          className="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[var(--secondary)] hover:bg-[var(--border)] hover:text-[var(--foreground)] transition-colors text-sm"
          title={!isExpanded ? (isPinned ? "Unpin" : "Pin") : undefined}
        >
          <i className={`fa-solid ${isPinned ? "fa-thumbtack" : "fa-thumbtack fa-rotate-90"} w-4 text-center flex-shrink-0 text-xs`}></i>
          <span className={`whitespace-nowrap transition-opacity duration-300 ${isExpanded ? "opacity-100" : "opacity-0"}`}>
            {isPinned ? "Unpin Sidebar" : "Pin Sidebar"}
          </span>
        </button>
      </div>
    </aside>
  );
}
