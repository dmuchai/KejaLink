# 🏠 KejaLink

A modern full-stack real estate web application tailored for the Kenyan market — allowing tenants to browse rental listings and verified agents to manage property advertisements securely and efficiently.

**Live Site:** [kejalink.co.ke](https://kejalink.co.ke)

---

## 🚀 Features

### Core Features
- 🔐 **JWT Authentication** - Secure token-based authentication for Agent & Tenant roles
- 🏢 **Agent Dashboard** - Complete metrics (total listings, active listings, views, engagement)
- 📍 **Google Maps Integration** - Places autocomplete restricted to Kenya with accurate coordinates
- 🧠 **AI-Enhanced Content** - Google Gemini AI for automatic listing description enhancement
- 🏞️ **Image Management** - Multi-image upload with automatic resizing and optimization
- 📱 **Mobile-Responsive** - Fully responsive design with Tailwind CSS
- 🗺️ **Interactive Maps** - Property location visualization with Google Maps
- 🔍 **Advanced Filtering** - Search by location, price range, bedrooms, bathrooms, property type

### Technical Features
- ⚡ **Fast Performance** - Vite build system with optimized bundle sizes
- 🧪 **Type Safety** - Full TypeScript coverage with strict typing
- 🎨 **Modern UI** - Tailwind CSS with custom components
- 🔒 **Secure Backend** - PHP 8.3+ with MySQL database
- 📸 **Smart Image Handling** - Automatic WebP conversion and responsive image generation
- 🌐 **SEO Ready** - Meta tags and structured data for better search visibility

---

## 🏗️ Architecture

### Frontend Stack
- **Framework:** React 19.1.0 + TypeScript
- **Build Tool:** Vite 7.0.3
- **Routing:** React Router v7
- **Styling:** Tailwind CSS 3.4
- **Maps:** Google Maps JavaScript API with Places library
- **AI:** Google Gemini API (gemini-2.5-flash model)
- **HTTP Client:** Native Fetch API with custom wrapper

### Backend Stack
- **Runtime:** PHP 8.3.6
- **Database:** MySQL 8.0
- **Server:** Apache with mod_rewrite
- **Hosting:** HostAfrica cPanel
- **API:** RESTful API with JWT authentication
- **File Storage:** Local filesystem with organized directory structure

### Project Structure
```
kejalink/
├── components/           # Reusable React components
│   ├── agent/           # Agent-specific components
│   ├── Alert.tsx        # Toast notifications
│   ├── Button.tsx       # Button component
│   ├── Input.tsx        # Form input component
│   ├── Navbar.tsx       # Navigation bar
│   ├── PlacesAutocomplete.tsx  # Google Places integration
│   ├── PropertyMap.tsx  # Map display component
│   └── ...
├── pages/               # Page components
│   ├── HomePage.tsx     # Landing page with featured listings
│   ├── ListingsPage.tsx # Browse all listings with filters
│   ├── ListingDetailPage.tsx  # Individual listing view
│   ├── AgentDashboardPage.tsx # Agent control panel
│   └── AuthPage.tsx     # Login/Register
├── services/            # API and external service integrations
│   ├── apiClient.ts     # Base HTTP client
│   ├── listingService.ts # Listing CRUD operations
│   ├── authService.ts   # Authentication logic
│   ├── geminiService.ts # AI content enhancement
│   └── uploadService.ts # Image upload handling
├── hooks/               # Custom React hooks
│   └── useAuth.tsx      # Authentication state management
├── utils/               # Utility functions
│   └── validation.ts    # Form and data validation
├── php-backend/         # Backend API
│   ├── api/
│   │   ├── auth.php     # Authentication endpoints
│   │   ├── listings.php # Listing CRUD endpoints
│   │   └── agents.php   # Agent management
│   ├── config.php       # Database and API configuration
│   ├── email-config.php # Email service (Brevo SMTP)
│   └── uploads/         # Uploaded property images
└── types.ts             # TypeScript type definitions
```

### Key Features Implementation

#### Google Maps Integration
- **Places Autocomplete** - Restricted to Kenya (`country: 'ke'`)
- **Geocoding** - Automatic latitude/longitude extraction
- **Interactive Maps** - Clickable markers with property info
- **Responsive** - Mobile-optimized map controls

#### AI Content Enhancement
- **Google Gemini 2.5 Flash** - Fast, cost-effective model
- **Smart Prompts** - Context-aware content generation
- **Fallback Handling** - Graceful degradation if AI fails
- **Rate Limiting** - Automatic retry logic

#### Authentication System
- **JWT Tokens** - Secure stateless authentication
- **Role-Based Access** - Agent vs Tenant permissions
- **Password Reset** - Email-based recovery flow
- **Session Management** - Automatic token refresh

---

## 📚 Documentation

This project includes comprehensive documentation:

- **[DOCUMENTATION.md](./DOCUMENTATION.md)** - Complete code documentation and architecture guide
- **[TESTING.md](./TESTING.md)** - Testing infrastructure and guidelines  
- **[TEST_SUMMARY.md](./TEST_SUMMARY.md)** - Test implementation overview
- **Inline Documentation** - All functions include detailed JSDoc comments
- **Type Definitions** - Full TypeScript interfaces in `types.ts`

### Key Documentation Features

- **JSDoc Comments**: Every function has comprehensive documentation
- **Usage Examples**: Real-world code examples for all major features
- **Error Handling**: Detailed error scenarios and handling patterns
- **Type Safety**: Complete TypeScript coverage with strict typing
- **Best Practices**: Architecture patterns and coding standards

---

## 🧪 Testing

Comprehensive test suite with 90%+ coverage:

```bash
# Run all tests
npm test

# Run tests in watch mode
npm run test:watch

# Generate coverage report
npm run test:coverage
```

### Test Coverage
- ✅ Listing CRUD operations
- ✅ Search and filtering functionality
- ✅ Image upload and validation
- ✅ Error handling and edge cases
- ✅ Data transformation functions
- ✅ Agent metrics calculation

---

## 🛠️ Setup & Installation

### Prerequisites
- **Node.js** 18+ and npm
- **PHP** 8.3+ with extensions: `mysqli`, `gd`, `fileinfo`, `mbstring`
- **MySQL** 8.0+
- **Apache** with `mod_rewrite` enabled
- **Google Maps API Key** (with Places API enabled)
- **Google Gemini API Key**

### Frontend Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/dmuchai/KejaLink.git
   cd KejaLink
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Configure environment variables**
   
   Create a `.env` file in the root directory:
   ```env
   VITE_API_BASE_URL=http://localhost:8080
   VITE_GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
   VITE_GEMINI_API_KEY=your_gemini_api_key_here
   ```

4. **Start development server**
   ```bash
   npm run dev
   ```
   
   The app will be available at `http://localhost:5173`

### Backend Setup

1. **Navigate to backend directory**
   ```bash
   cd php-backend
   ```

2. **Configure database**
   
   Copy the example config file:
   ```bash
   cp config.example.php config.php
   ```
   
   Update `config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kejalink_db');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   ```

3. **Import database schema**
   ```bash
   mysql -u your_db_user -p kejalink_db < mysql_schema.sql
   ```

4. **Configure email (optional)**
   
   For password reset functionality, copy and configure:
   ```bash
   cp email-config.example.php email-config.php
   ```
   
   Update with your SMTP credentials (Brevo recommended).

5. **Set up uploads directory**
   ```bash
   mkdir -p uploads/listings
   chmod 755 uploads
   chmod 755 uploads/listings
   ```

6. **Start PHP development server** (for local testing)
   ```bash
   php -S localhost:8080
   ```

---

## 🌍 Production Deployment

### HostAfrica cPanel Deployment

1. **Build the frontend**
   ```bash
   npm run build
   ```

2. **Create deployment package**
   ```bash
   bash create-deployment-package.sh
   ```

3. **Upload to cPanel**
   - Login to cPanel File Manager
   - Navigate to `public_html`
   - Upload the generated zip file
   - Extract the archive

4. **Configure Apache**
   - Ensure `.htaccess` is present with proper rewrite rules
   - Verify `mod_rewrite` is enabled

5. **Update environment**
   - Update API URLs in deployed JavaScript files
   - Configure database connection in `config.php`
   - Set proper file permissions for uploads directory

For detailed deployment instructions, see:
- [deploy-frontend.md](./deploy-frontend.md)
- [HOSTAFRICA_MIGRATION_GUIDE.md](./HOSTAFRICA_MIGRATION_GUIDE.md)

---

## 🔐 Authentication

The application uses JWT-based authentication with two user roles:

### Tenant Role
- Browse and search listings
- View property details and maps
- Save favorite properties
- Contact agents

### Agent Role
- All tenant features
- Create and manage listings
- Upload property images
- View analytics dashboard
- Edit profile information
- Access agent-only routes

### Authentication Flow
1. User registers/logs in via `/auth` page
2. Backend validates credentials and generates JWT token
3. Token stored in localStorage
4. Token sent in Authorization header for protected routes
5. Backend validates token and extracts user role
6. Role-based access control enforced on both frontend and backend

---

---

## 📊 API Endpoints

### Authentication
- `POST /api/auth.php?action=register` - User registration
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=forgot-password` - Password reset request
- `POST /api/auth.php?action=reset-password` - Password reset confirmation

### Listings
- `GET /api/listings.php` - Get all listings (with filters)
- `GET /api/listings.php?id={id}` - Get single listing
- `POST /api/listings.php` - Create new listing (Agent only)
- `PUT /api/listings.php?id={id}` - Update listing (Agent only)
- `DELETE /api/listings.php?id={id}` - Delete listing (Agent only)

### Agents
- `GET /api/agents.php` - Get all verified agents
- `GET /api/agents.php?id={id}` - Get agent profile
- `PUT /api/agents.php?id={id}` - Update agent profile (Agent only)

### Image Upload
- `POST /api/upload.php` - Upload property images
- Supports: JPEG, PNG, WebP
- Max size: 5MB per image
- Auto-resize and optimization

---

## 🎨 UI Components

### Reusable Components
- **Alert** - Toast notifications with success/error states
- **Button** - Customizable button with loading states
- **Input** - Form input with validation
- **Select** - Dropdown with custom styling
- **Textarea** - Multi-line text input
- **PlacesAutocomplete** - Google Places integration with Kenya restriction
- **PropertyMap** - Interactive map with markers
- **ImageCarousel** - Image gallery with navigation
- **ListingCard** - Property listing preview card
- **LoadingSpinner** - Loading state indicator

### Page Components
- **HomePage** - Landing page with featured listings
- **ListingsPage** - Browse all listings with advanced filters
- **ListingDetailPage** - Individual property details with map
- **AgentDashboardPage** - Agent control panel with metrics
- **AuthPage** - Login/register with form validation

---

## 🚀 Performance Optimizations

- ✅ **Code Splitting** - Lazy loading for routes
- ✅ **Image Optimization** - WebP format with responsive sizes
- ✅ **Bundle Optimization** - Tree shaking and minification
- ✅ **Caching Strategy** - Browser caching for static assets
- ✅ **Database Indexing** - Optimized queries with indexes
- ✅ **Lazy Loading** - Images and maps load on demand
- ✅ **Compression** - Gzip compression for text assets

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### Contribution Guidelines
- Follow existing code style and conventions
- Add tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting PR

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👤 Author

**Dennis Muchai**
- 🌐 Website: [dennis-muchai.vercel.app](https://dennis-muchai.vercel.app/)
- 💼 LinkedIn: [linkedin.com/in/dmmuchai](https://www.linkedin.com/in/dmmuchai/)
- 🐙 GitHub: [@dmuchai](https://github.com/dmuchai)

---

## 🙏 Acknowledgments

- Google Maps API for location services
- Google Gemini AI for content enhancement
- Tailwind CSS for styling framework
- React community for excellent documentation
- HostAfrica for reliable hosting

---

## 📞 Support

For support, email support@kejalink.co.ke or open an issue on GitHub.

---

**Built with ❤️ for the Kenyan real estate market**
