import prisma from "./prisma";

export type SettingKey = 
  | "gemini_api_key" 
  | "tinymce_api_key" 
  | "last_model" 
  | "default_page_size" 
  | "default_page_orientation";

const ENV_MAPPING: Record<string, string> = {
  gemini_api_key: "GEMINI_API_KEY",
  tinymce_api_key: "TINYMCE_API_KEY",
  last_model: "DEFAULT_AI_MODEL",
};

export async function getSetting(key: SettingKey): Promise<string | null> {
  // First try database
  if (prisma) {
    const dbSetting = await prisma.setting.findUnique({
      where: { key },
    });

    if (dbSetting?.value) {
      return dbSetting.value;
    }
  }

  // Fallback to environment variable
  const envKey = ENV_MAPPING[key];
  if (envKey && process.env[envKey]) {
    return process.env[envKey] || null;
  }

  return null;
}

export async function setSetting(key: SettingKey, value: string): Promise<void> {
  if (prisma) {
    await prisma.setting.upsert({
      where: { key },
      update: { value },
      create: { key, value },
    });
  }
}

export async function getAllSettings(): Promise<Record<string, string>> {
  let settings: any[] = [];
  if (prisma) {
    settings = await prisma.setting.findMany();
  }
  
  const result: Record<string, string> = {};

  // Start with env defaults
  for (const [dbKey, envKey] of Object.entries(ENV_MAPPING)) {
    if (process.env[envKey]) {
      result[dbKey] = process.env[envKey] || "";
    }
  }

  // Override with database values
  for (const setting of settings) {
    result[setting.key] = setting.value;
  }

  return result;
}

export async function getGeminiApiKey(): Promise<string | null> {
  return getSetting("gemini_api_key");
}

export async function getTinyMCEApiKey(): Promise<string | null> {
  return getSetting("tinymce_api_key");
}

export async function getLastModel(): Promise<string> {
  const model = await getSetting("last_model");
  return model || "gemini-2.0-flash";
}

export async function setLastModel(model: string): Promise<void> {
  return setSetting("last_model", model);
}
