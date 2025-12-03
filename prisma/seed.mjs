import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

async function main() {
  console.log("Seeding database...");

  // Create default templates
  const templates = [
    {
      name: "Blank Document",
      slug: "blank",
      description: "Start with a blank document",
      type: "blank",
      isSystem: true,
      isActive: true,
      content: JSON.stringify({ content: "" }),
    },
    {
      name: "Business Letter",
      slug: "business-letter",
      description: "Professional business letter template",
      type: "docx",
      isSystem: true,
      isActive: true,
      content: JSON.stringify({
        content: `<p>[Your Company Name]</p>
<p>[Your Address]</p>
<p>[City, State ZIP]</p>
<p>[Date]</p>
<br/>
<p>[Recipient Name]</p>
<p>[Recipient Title]</p>
<p>[Company Name]</p>
<p>[Address]</p>
<p>[City, State ZIP]</p>
<br/>
<p>Dear [Recipient Name],</p>
<br/>
<p>[Opening paragraph - State the purpose of the letter]</p>
<br/>
<p>[Body paragraph - Provide details and supporting information]</p>
<br/>
<p>[Closing paragraph - Summarize and state next steps]</p>
<br/>
<p>Sincerely,</p>
<br/>
<p>[Your Name]</p>
<p>[Your Title]</p>`,
      }),
    },
    {
      name: "Meeting Minutes",
      slug: "meeting-minutes",
      description: "Template for recording meeting minutes",
      type: "docx",
      isSystem: true,
      isActive: true,
      content: JSON.stringify({
        content: `<h1>Meeting Minutes</h1>
<br/>
<p><strong>Date:</strong> [Date]</p>
<p><strong>Time:</strong> [Start Time] - [End Time]</p>
<p><strong>Location:</strong> [Location/Virtual]</p>
<p><strong>Attendees:</strong> [List of attendees]</p>
<br/>
<h2>Agenda</h2>
<ol>
<li>[Agenda item 1]</li>
<li>[Agenda item 2]</li>
<li>[Agenda item 3]</li>
</ol>
<br/>
<h2>Discussion</h2>
<p>[Summary of discussions]</p>
<br/>
<h2>Action Items</h2>
<ul>
<li>[Action item 1] - Assigned to: [Name] - Due: [Date]</li>
<li>[Action item 2] - Assigned to: [Name] - Due: [Date]</li>
</ul>
<br/>
<h2>Next Meeting</h2>
<p>Date: [Next meeting date]</p>
<p>Time: [Next meeting time]</p>`,
      }),
    },
    {
      name: "Project Proposal",
      slug: "project-proposal",
      description: "Template for project proposals",
      type: "docx",
      isSystem: true,
      isActive: true,
      content: JSON.stringify({
        content: `<h1>Project Proposal</h1>
<p><strong>Project Name:</strong> [Project Name]</p>
<p><strong>Date:</strong> [Date]</p>
<p><strong>Prepared by:</strong> [Your Name]</p>
<br/>
<h2>Executive Summary</h2>
<p>[Brief overview of the project]</p>
<br/>
<h2>Project Background</h2>
<p>[Context and background information]</p>
<br/>
<h2>Objectives</h2>
<ul>
<li>[Objective 1]</li>
<li>[Objective 2]</li>
<li>[Objective 3]</li>
</ul>
<br/>
<h2>Scope</h2>
<p>[Define what is included and excluded]</p>
<br/>
<h2>Timeline</h2>
<p>[Project timeline and milestones]</p>
<br/>
<h2>Budget</h2>
<p>[Estimated budget and resources]</p>
<br/>
<h2>Conclusion</h2>
<p>[Summary and call to action]</p>`,
      }),
    },
    {
      name: "Report Template",
      slug: "report",
      description: "General report template",
      type: "docx",
      isSystem: true,
      isActive: true,
      content: JSON.stringify({
        content: `<h1>[Report Title]</h1>
<p><strong>Author:</strong> [Your Name]</p>
<p><strong>Date:</strong> [Date]</p>
<br/>
<h2>Table of Contents</h2>
<ol>
<li>Introduction</li>
<li>Methodology</li>
<li>Findings</li>
<li>Analysis</li>
<li>Recommendations</li>
<li>Conclusion</li>
</ol>
<br/>
<h2>1. Introduction</h2>
<p>[Introduce the topic and purpose of the report]</p>
<br/>
<h2>2. Methodology</h2>
<p>[Describe how data was collected and analyzed]</p>
<br/>
<h2>3. Findings</h2>
<p>[Present the key findings]</p>
<br/>
<h2>4. Analysis</h2>
<p>[Analyze and interpret the findings]</p>
<br/>
<h2>5. Recommendations</h2>
<p>[Provide actionable recommendations]</p>
<br/>
<h2>6. Conclusion</h2>
<p>[Summarize the report]</p>`,
      }),
    },
  ];

  for (const template of templates) {
    await prisma.template.upsert({
      where: { slug: template.slug },
      update: template,
      create: template,
    });
  }

  console.log(`Created ${templates.length} templates`);

  // Create default settings
  const settings = [
    { key: "last_model", value: "gemini-2.5-flash" },
    { key: "default_page_size", value: "a4" },
    { key: "default_page_orientation", value: "portrait" },
  ];

  for (const setting of settings) {
    await prisma.setting.upsert({
      where: { key: setting.key },
      update: { value: setting.value },
      create: setting,
    });
  }

  console.log(`Created ${settings.length} default settings`);

  console.log("Seeding completed!");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
