"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const navItems = [
  { href: "/", label: "Dashboard", icon: "fa-solid fa-house" },
  { href: "/documents", label: "Documents", icon: "fa-solid fa-file-lines" },
  { href: "/files", label: "File Manager", icon: "fa-solid fa-folder-open" },
  { href: "/settings", label: "Settings", icon: "fa-solid fa-gear" },
  { href: "/about", label: "About", icon: "fa-solid fa-circle-info" },
];

export default function Navbar() {
  const pathname = usePathname();

  return (
    <nav className="bg-[var(--card)] border-b border-[var(--border)] sticky top-0 z-40">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2 font-semibold text-lg">
            <i className="fa-solid fa-file-signature text-[var(--primary)]"></i>
            <span>ElectronicSheet</span>
          </Link>

          {/* Navigation Links */}
          <div className="flex items-center gap-1">
            {navItems.map((item) => {
              const isActive = pathname === item.href || 
                (item.href !== "/" && pathname.startsWith(item.href));
              
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`
                    flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium transition-colors
                    ${isActive 
                      ? "bg-[var(--primary)] text-white" 
                      : "text-[var(--foreground)] hover:bg-[var(--border)]"
                    }
                  `}
                >
                  <i className={item.icon}></i>
                  <span className="hidden sm:inline">{item.label}</span>
                </Link>
              );
            })}
          </div>
        </div>
      </div>
    </nav>
  );
}
