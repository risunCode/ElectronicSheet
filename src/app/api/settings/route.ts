import { NextResponse } from "next/server";
import prisma from "@/lib/prisma";

export async function GET() {
  try {
    const settings = await prisma.setting.findMany();
    const result: Record<string, string> = {};

    // Start with env defaults
    const envDefaults: Record<string, string | undefined> = {
      gemini_api_key: process.env.GEMINI_API_KEY,
      tinymce_api_key: process.env.TINYMCE_API_KEY,
      last_model: process.env.DEFAULT_AI_MODEL || "gemini-2.0-flash",
    };

    for (const [key, value] of Object.entries(envDefaults)) {
      if (value) result[key] = value;
    }

    // Override with database values
    for (const setting of settings) {
      result[setting.key] = setting.value;
    }

    return NextResponse.json(result);
  } catch (error) {
    console.error("Error fetching settings:", error);
    return NextResponse.json({ error: "Failed to fetch settings" }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { key, value } = body;

    if (!key || value === undefined) {
      return NextResponse.json({ error: "Key and value are required" }, { status: 400 });
    }

    const setting = await prisma.setting.upsert({
      where: { key },
      update: { value },
      create: { key, value },
    });

    return NextResponse.json(setting);
  } catch (error) {
    console.error("Error saving setting:", error);
    return NextResponse.json({ error: "Failed to save setting" }, { status: 500 });
  }
}
