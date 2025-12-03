"use client";

import { useState, useEffect } from "react";
import { useRouter, usePathname } from "next/navigation";

export default function Header() {
  const [isDark, setIsDark] = useState(false);
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
      setIsDark(true);
      document.documentElement.classList.add("dark");
    }
  }, []);

  const toggleTheme = () => {
    const newDark = !isDark;
    setIsDark(newDark);
    localStorage.setItem("theme", newDark ? "dark" : "light");
    if (newDark) {
      document.documentElement.classList.add("dark");
    } else {
      document.documentElement.classList.remove("dark");
    }
  };

  // Get current page title based on pathname
  const getCurrentPageTitle = () => {
    if (pathname === "/") return "Dashboard";
    if (pathname === "/documents") return "Documents";
    if (pathname.startsWith("/documents/")) return "Document Editor";
    if (pathname === "/files") return "Files";
    if (pathname === "/settings") return "Settings";
    if (pathname === "/about") return "About";
    return "ElectronicSheet";
  };

  const getCurrentTime = () => {
    return new Date().toLocaleTimeString("en-US", { 
      hour12: false, 
      hour: "2-digit", 
      minute: "2-digit" 
    });
  };

  const [currentTime, setCurrentTime] = useState(getCurrentTime());

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentTime(getCurrentTime());
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  return (
    <header className="app-header">
      <div className="flex items-center justify-between w-full">
        {/* Left: Page Info */}
        <div className="flex items-center gap-4">
          <div className="flex items-center gap-2">
            <i className="fa-solid fa-layer-group text-[var(--accent)] text-lg"></i>
            <span className="font-semibold text-[var(--foreground)]">ElectronicSheet</span>
          </div>
          <div className="hidden md:flex items-center gap-2 text-sm text-[var(--secondary)]">
            <span>/</span>
            <span>{getCurrentPageTitle()}</span>
          </div>
        </div>


        {/* Right: System Info & Controls */}
        <div className="flex items-center gap-3">
          <div className="hidden sm:flex items-center gap-3 text-xs text-[var(--secondary)]">
            <span className="flex items-center gap-1">
              <i className="fa-solid fa-clock"></i>
              {currentTime}
            </span>
            <span className="flex items-center gap-1">
              <i className="fa-solid fa-wifi"></i>
              Online
            </span>
          </div>
          
          <div className="w-px h-4 bg-[var(--border)] hidden sm:block"></div>
          
          <button
            onClick={toggleTheme}
            className="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm hover:bg-[var(--border)] transition-colors"
            title={isDark ? "Switch to Light Mode" : "Switch to Dark Mode"}
          >
            <i className={`fa-solid ${isDark ? "fa-sun" : "fa-moon"} text-[var(--secondary)]`}></i>
            <span className="hidden sm:inline text-[var(--secondary)]">{isDark ? "Light" : "Dark"}</span>
          </button>
        </div>
      </div>
    </header>
  );
}
