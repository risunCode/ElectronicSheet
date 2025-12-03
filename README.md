# ElectronicSheet 📄

> Modern Document Management System with AI Integration

A powerful, AI-powered document editor built with Next.js 14 and Google Gemini. Create, edit, and enhance your documents with intelligent writing assistance.

![ElectronicSheet](https://img.shields.io/badge/Next.js-14-black?style=for-the-badge&logo=next.js)
![TypeScript](https://img.shields.io/badge/TypeScript-007ACC?style=for-the-badge&logo=typescript&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Gemini AI](https://img.shields.io/badge/Gemini-AI-4285F4?style=for-the-badge&logo=google&logoColor=white)

## ✨ Features

### 🖊️ Document Editor
- **TinyMCE Integration**: Full-featured WYSIWYG editor
- **Table Support**: Create and edit tables with proper formatting  
- **Multiple Page Formats**: A4, F4, Legal, and Letter sizes
- **Real-time Auto-save**: Never lose your work
- **Export Options**: PDF and DOC export capabilities

### 🤖 AI Assistant
- **Smart Writing**: Generate content from prompts
- **Content Enhancement**: Improve grammar and structure
- **Auto-continuation**: Continue writing seamlessly
- **Summarization**: Create concise summaries
- **Translation**: Multi-language support
- **Knowledge Base**: Pattern-based table generation

### 📁 File Management
- **Upload & Organize**: Drag-and-drop file uploads
- **File Browser**: Intuitive file navigation
- **Multiple Formats**: Support for various file types

### 🎨 Modern UI/UX
- **Responsive Design**: Works on all screen sizes
- **Dark/Light Theme**: Toggle between themes
- **Clean Interface**: Professional, distraction-free design
- **Real-time Updates**: Live clock and status indicators

## 🚀 Quick Start

### Prerequisites
- Node.js 18+ 
- npm or yarn
- Git
- Google Gemini API key
- TinyMCE API key

### Local Development

#### Option 1: Standard Setup
```bash
# Clone the repository
git clone https://github.com/risunCode/ElectronicSheet.git
cd ElectronicSheet

# Install dependencies
npm install

# Set up environment variables
cp .env.example .env.local

# Edit .env.local and configure your API keys:
# - GEMINI_API_KEY=your_gemini_api_key_here
# - TINYMCE_API_KEY=your_tinymce_api_key_here
# - DATABASE_URL="file:./dev.db"

# Initialize database
npx prisma generate
npx prisma db push

# Start development server
npm run dev

# Open http://localhost:3000
```

#### Option 2: Laragon Setup (Windows)
```bash
# 1. Install Laragon and start services
# 2. Clone in laragon/www directory
cd C:\laragon\www
git clone https://github.com/risunCode/ElectronicSheet.git
cd ElectronicSheet

# 3. Install dependencies
npm install

# 4. Set up environment
cp .env.example .env.local
# Edit .env.local with your API keys

# 5. Database setup
npx prisma generate
npx prisma db push

# 6. Start development
npm run dev

# 7. Access via: http://localhost:3000 or http://electronicsheet.test (with Laragon virtual host)
```

### 🌍 Deployment

#### Deploy to Vercel
```bash
# 1. Install Vercel CLI
npm i -g vercel

# 2. Login to Vercel
vercel login

# 3. Deploy
vercel --prod

# 4. Set environment variables in Vercel dashboard:
# - GEMINI_API_KEY
# - TINYMCE_API_KEY  
# - DATABASE_URL (use Vercel Postgres or external DB)
```

#### Deploy to Netlify
```bash
# 1. Build the project
npm run build

# 2. Deploy to Netlify
# - Connect GitHub repository
# - Set build command: npm run build
# - Set publish directory: .next
# - Add environment variables in Netlify dashboard
```

## 🔧 Configuration

### Environment Variables
Create `.env.local` file in project root:

```env
# Required
GEMINI_API_KEY=your_gemini_api_key_here
TINYMCE_API_KEY=your_tinymce_api_key_here

# Database
DATABASE_URL="file:./dev.db"

# Optional
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

### Getting API Keys

#### Google Gemini API Key
1. Visit [Google AI Studio](https://aistudio.google.com/)
2. Create a new project or select existing
3. Generate API key
4. Add to `.env.local`

#### TinyMCE API Key
1. Visit [TinyMCE](https://www.tiny.cloud/)
2. Sign up for free account
3. Get API key from dashboard
4. Add to `.env.local`

## 📖 Usage Guide

### Basic Document Creation
1. Navigate to **Documents** page
2. Click **"Create New Document"**
3. Choose paper format and orientation
4. Start writing with TinyMCE editor
5. Use AI assistant for content enhancement

### AI Writing Actions
- **Write**: `"Create a business proposal outline"`
- **Continue**: Select text and click Continue
- **Improve**: Highlight text to enhance
- **Summarize**: Generate document summary
- **Translate**: Convert to different languages
- **Knowledge**: Get structured table patterns

### Custom AI Instructions
1. Click **"View Instructions"** button
2. Navigate to desired action
3. Customize prompts with placeholders:
   - `[content]` - User's text input
   - `[language]` - Target language for translation

### File Management
1. Go to **Files** section
2. Upload files via drag-and-drop
3. Organize in folders
4. Link files to documents

## 🛠️ Development

### Tech Stack
- **Framework**: Next.js 14 with App Router
- **Language**: TypeScript
- **Styling**: TailwindCSS
- **Database**: Prisma ORM + SQLite
- **AI**: Google Gemini API
- **Editor**: TinyMCE
- **UI**: Custom components + Font Awesome
- **Deployment**: Vercel/Netlify ready
 
### Available Scripts
```bash
npm run dev          # Start development server
npm run build        # Build for production
npm run start        # Start production server
npm run lint         # Run ESLint
npm run type-check   # TypeScript type checking
```

### Database Management
```bash
# View data
npx prisma studio

# Reset database
npx prisma db push --force-reset

# Generate client
npx prisma generate

# Run migrations (production)
npx prisma migrate deploy
```

### Git Setup & Best Practices
```bash
# Initialize repository (if starting fresh)
git init
git add .
git commit -m "Initial commit"

# Add remote origin
git remote add origin https://github.com/risunCode/ElectronicSheet.git
git branch -M main
git push -u origin main

# Development workflow
git checkout -b feature/your-feature
git add .
git commit -m "Add your feature"
git push origin feature/your-feature

# Create Pull Request on GitHub
```

### Important Files
- `.env.example` - Environment variables template
- `.gitignore` - Git ignore patterns (includes laravel-old)
- `prisma/schema.prisma` - Database schema
- `LICENSE` - MIT License file

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**risunCode**
- GitHub: [@risunCode](https://github.com/risunCode)
- Project: [ElectronicSheet](https://github.com/risunCode/ElectronicSheet)

## 🙏 Acknowledgments

- [Next.js](https://nextjs.org/) - React framework
- [TinyMCE](https://www.tiny.cloud/) - Rich text editor
- [Google Gemini](https://ai.google.dev/) - AI language model
- [Prisma](https://prisma.io/) - Database ORM
- [TailwindCSS](https://tailwindcss.com/) - CSS framework
- [Font Awesome](https://fontawesome.com/) - Icons

---

⭐ **Star this repo if you find it helpful!**
