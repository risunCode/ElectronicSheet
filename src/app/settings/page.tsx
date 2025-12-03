"use client";

import { useState, useEffect } from "react";
import Swal from "sweetalert2";

interface Settings {
  gemini_api_key: string;
  tinymce_api_key: string;
  last_model: string;
  custom_model: string;
}

export default function SettingsPage() {
  const [settings, setSettings] = useState<Settings>({
    gemini_api_key: "",
    tinymce_api_key: "",
    last_model: "gemini-2.5-flash",
    custom_model: "",
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchSettings();
  }, []);

  async function fetchSettings() {
    try {
      const res = await fetch("/api/settings");
      const data = await res.json();
      const savedModel = data.last_model || "gemini-2.5-flash";
      const presetModels = ["gemini-flash-latest", "gemini-2.0-flash", "gemini-2.5-flash", "gemini-2.5-flash-lite", "gemini-2.5-pro", "gemini-3-pro-preview"];
      setSettings({
        gemini_api_key: data.gemini_api_key || "",
        tinymce_api_key: data.tinymce_api_key || "",
        last_model: presetModels.includes(savedModel) ? savedModel : "custom",
        custom_model: presetModels.includes(savedModel) ? "" : savedModel,
      });
    } catch (error) {
      console.error("Failed to fetch settings:", error);
    } finally {
      setLoading(false);
    }
  }

  async function saveSetting(key: string, value: string) {
    setSaving(true);
    try {
      const res = await fetch("/api/settings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ key, value }),
      });

      if (!res.ok) throw new Error("Failed to save");

      await Swal.fire({
        icon: "success",
        title: "Saved",
        text: "Setting has been saved successfully.",
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Failed to save setting:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to save setting. Please try again.",
      });
    } finally {
      setSaving(false);
    }
  }

  async function handleSaveAll() {
    setSaving(true);
    try {
      await Promise.all([
        fetch("/api/settings", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ key: "gemini_api_key", value: settings.gemini_api_key }),
        }),
        fetch("/api/settings", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ key: "tinymce_api_key", value: settings.tinymce_api_key }),
        }),
        fetch("/api/settings", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ key: "last_model", value: settings.last_model === "custom" ? settings.custom_model : settings.last_model }),
        }),
      ]);

      await Swal.fire({
        icon: "success",
        title: "Saved",
        text: "All settings have been saved successfully.",
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Failed to save settings:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to save settings. Please try again.",
      });
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-center py-12">
          <i className="fa-solid fa-spinner fa-spin text-2xl text-[var(--primary)]"></i>
        </div>
      </div>
    );
  }

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-semibold">Settings</h1>
          <p className="text-[var(--secondary)] mt-1">Konfigurasi API keys dan pengaturan aplikasi</p>
        </div>
        <button
          onClick={handleSaveAll}
          disabled={saving}
          className="btn btn-primary"
        >
          {saving ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-floppy-disk"></i>}
          Save All Settings
        </button>
      </div>

      {/* Grid Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Gemini API Key */}
        <div className="card">
          <h3 className="font-semibold mb-4 flex items-center gap-2">
            <i className="fa-solid fa-key text-[var(--accent)]"></i>
            Gemini API Key
          </h3>
          <input
            type="password"
            className="input mb-3"
            placeholder="Enter your Gemini API key"
            value={settings.gemini_api_key}
            onChange={(e) => setSettings({ ...settings, gemini_api_key: e.target.value })}
          />
          <p className="text-sm text-[var(--secondary)]">
            Get your API key from{" "}
            <a href="https://makersuite.google.com/app/apikey" target="_blank" rel="noopener noreferrer" className="text-[var(--accent)] hover:underline">
              Google AI Studio
            </a>
          </p>
        </div>

        {/* TinyMCE API Key */}
        <div className="card">
          <h3 className="font-semibold mb-4 flex items-center gap-2">
            <i className="fa-solid fa-pen-nib text-[var(--accent)]"></i>
            TinyMCE API Key
          </h3>
          <input
            type="password"
            className="input mb-3"
            placeholder="Enter your TinyMCE API key"
            value={settings.tinymce_api_key}
            onChange={(e) => setSettings({ ...settings, tinymce_api_key: e.target.value })}
          />
          <p className="text-sm text-[var(--secondary)]">
            Get your API key from{" "}
            <a href="https://www.tiny.cloud/auth/signup/" target="_blank" rel="noopener noreferrer" className="text-[var(--accent)] hover:underline">
              TinyMCE
            </a>
          </p>
        </div>

        {/* Default AI Model */}
        <div className="card">
          <h3 className="font-semibold mb-4 flex items-center gap-2">
            <i className="fa-solid fa-robot text-[var(--accent)]"></i>
            Default AI Model
          </h3>
          <select
            className="input"
            value={settings.last_model}
            onChange={(e) => setSettings({ ...settings, last_model: e.target.value })}
          >
            <option value="gemini-flash-latest">Gemini Flash Latest</option>
            <option value="gemini-2.0-flash">Gemini 2.0 Flash</option>
            <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
            <option value="gemini-2.5-flash-lite">Gemini 2.5 Flash Lite</option>
            <option value="gemini-2.5-pro">Gemini 2.5 Pro</option>
            <option value="gemini-3-pro-preview">Gemini 3 Pro Preview</option>
            <option value="custom">Custom Model</option>
          </select>
          <p className="text-sm text-[var(--secondary)] mt-3">
            Select the default model for AI writing assistant
          </p>
        </div>

        {/* Custom Model (conditional) */}
        {settings.last_model === "custom" && (
          <div className="card">
            <h3 className="font-semibold mb-4 flex items-center gap-2">
              <i className="fa-solid fa-terminal text-[var(--accent)]"></i>
              Custom Model Name
            </h3>
            <input
              type="text"
              className="input"
              placeholder="e.g., gemini-2.0-flash"
              value={settings.custom_model}
              onChange={(e) => setSettings({ ...settings, custom_model: e.target.value })}
            />
            <p className="text-sm text-[var(--secondary)] mt-3">
              Enter the exact model name from Google AI
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
