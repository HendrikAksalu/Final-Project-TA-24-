# Photo Album Application

A full-stack photo album application built with Vue.js 3 and Node.js + Express.

## Features

- 📸 Create and manage photo albums
- 🖼️ Upload and organize photos
- 🔍 Lightbox view for full-size photos
- 🎨 Responsive design with Tailwind CSS
- 🚀 Modern Vue.js 3 with Composition API
- 💾 MongoDB database integration

## Tech Stack

### Frontend
- **Vue.js 3** with Composition API
- **Vite** - Fast build tool
- **Tailwind CSS** - Utility-first CSS framework
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **Axios** - HTTP client

### Backend
- **Node.js** with Express
- **MongoDB** with Mongoose
- **Multer** - File upload handling
- **CORS** - Cross-origin resource sharing

## Project Structure

```
/
├── client/                 # Vue.js frontend
│   ├── src/
│   │   ├── components/     # Reusable components
│   │   │   ├── Navbar.vue
│   │   │   ├── AlbumCard.vue
│   │   │   ├── PhotoCard.vue
│   │   │   └── Lightbox.vue
│   │   ├── views/          # Page views
│   │   │   ├── Home.vue
│   │   │   ├── Album.vue
│   │   │   └── Upload.vue
│   │   ├── router/         # Vue Router config
│   │   ├── stores/         # Pinia stores
│   │   ├── services/       # API service
│   │   ├── App.vue
│   │   └── main.js
│   ├── package.json
│   └── vite.config.js
├── server/                 # Node.js + Express backend
│   ├── controllers/        # Route handlers
│   │   ├── albumController.js
│   │   └── photoController.js
│   ├── models/             # Mongoose models
│   │   ├── Album.js
│   │   └── Photo.js
│   ├── routes/             # Express routes
│   │   ├── albums.js
│   │   └── photos.js
│   ├── middleware/         # Multer upload config
│   │   └── upload.js
│   ├── uploads/            # Uploaded image storage
│   ├── server.js           # Entry point
│   └── package.json
├── .env.example            # Environment variable template
├── .gitignore
└── README.md
```

## Prerequisites

Before you begin, ensure you have the following installed:
- **Node.js** (v16 or higher)
- **npm** or **yarn**
- **MongoDB** (local installation or MongoDB Atlas account)

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/HendrikAksalu/Final-Project-TA-24-.git
cd Final-Project-TA-24-
```

### 2. Environment Configuration

Create a `.env` file in the root directory based on `.env.example`:

```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:

```env
# Server Configuration
PORT=5000
NODE_ENV=development

# MongoDB Configuration
MONGODB_URI=mongodb://localhost:27017/photo-album

# Frontend URL (for CORS)
CLIENT_URL=http://localhost:5173

# Upload Configuration
UPLOAD_DIR=./uploads
MAX_FILE_SIZE=5242880
```

### 3. Backend Setup

```bash
# Navigate to server directory
cd server

# Install dependencies
npm install

# Start the development server
npm run dev
```

The backend server will start on `http://localhost:5000`

### 4. Frontend Setup

Open a new terminal window:

```bash
# Navigate to client directory
cd client

# Install dependencies
npm install

# Start the development server
npm run dev
```

The frontend application will start on `http://localhost:5173`

### 5. MongoDB Setup

#### Option A: Local MongoDB

1. Install MongoDB on your system
2. Start MongoDB service:
   ```bash
   mongod
   ```

#### Option B: MongoDB Atlas (Cloud)

1. Create a free account at [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
2. Create a new cluster
3. Get your connection string
4. Update `MONGODB_URI` in `.env` file:
   ```env
   MONGODB_URI=mongodb+srv://username:password@cluster.mongodb.net/photo-album
   ```

## Usage

1. **Open the application** in your browser at `http://localhost:5173`
2. **Create an album** by clicking "Create Album" on the home page
3. **Upload photos** by clicking on an album and then "Add Photos"
4. **View photos** in lightbox mode by clicking on any photo
5. **Delete photos or albums** using the delete buttons

## API Endpoints

### Albums
- `GET /api/albums` - List all albums
- `POST /api/albums` - Create a new album
- `GET /api/albums/:id` - Get a specific album
- `DELETE /api/albums/:id` - Delete an album

### Photos
- `GET /api/photos` - List all photos (optionally filter by album)
- `POST /api/photos` - Upload a new photo
- `DELETE /api/photos/:id` - Delete a photo

### Health Check
- `GET /api/health` - Server health check

## Development

### Running Tests

```bash
# Backend tests
cd server
npm test

# Frontend tests
cd client
npm test
```

### Building for Production

```bash
# Build frontend
cd client
npm run build

# The built files will be in client/dist/
```

### Production Deployment

1. Build the frontend application
2. Set `NODE_ENV=production` in `.env`
3. Use a process manager like PM2 to run the server:
   ```bash
   npm install -g pm2
   cd server
   pm2 start server.js --name photo-album
   ```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

ISC

## Author

Hendrik Aksalu

## Links

- Confluence: https://ametikool-team-xmqdihqh.atlassian.net/wiki/spaces/Fotoalbum/pages/edit-v2/52625666
- Jira: https://aksaluhendrik.atlassian.net/jira/software/projects/FOT/boards/7

