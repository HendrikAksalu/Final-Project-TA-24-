# Quick Start Guide

This guide will help you quickly set up and run the Photo Album application.

## Prerequisites

Make sure you have the following installed:
- Node.js (v16 or higher)
- MongoDB (local or MongoDB Atlas)

## Quick Setup

### 1. Install Dependencies

```bash
# Install backend dependencies
cd server
npm install

# Install frontend dependencies
cd ../client
npm install
```

### 2. Configure Environment

Create a `.env` file in the root directory:

```bash
cp .env.example .env
```

Edit the `.env` file with your MongoDB connection string:

```env
MONGODB_URI=mongodb://localhost:27017/photo-album
# OR for MongoDB Atlas:
# MONGODB_URI=mongodb+srv://username:password@cluster.mongodb.net/photo-album
```

### 3. Start the Application

Open two terminal windows:

**Terminal 1 - Backend:**
```bash
cd server
npm run dev
```

**Terminal 2 - Frontend:**
```bash
cd client
npm run dev
```

### 4. Access the Application

Open your browser and navigate to:
```
http://localhost:5173
```

## Features

- ✅ Create photo albums
- ✅ Upload photos to albums
- ✅ View photos in a responsive grid
- ✅ Lightbox view for full-size photos
- ✅ Delete photos and albums
- ✅ Rate-limited API for security
- ✅ Responsive design with Tailwind CSS

## API Endpoints

### Albums
- `GET /api/albums` - List all albums
- `POST /api/albums` - Create a new album
- `GET /api/albums/:id` - Get a specific album
- `DELETE /api/albums/:id` - Delete an album

### Photos
- `GET /api/photos?albumId=<id>` - List photos in an album
- `POST /api/photos` - Upload a photo
- `DELETE /api/photos/:id` - Delete a photo

## Troubleshooting

### MongoDB Connection Issues
- Make sure MongoDB is running locally: `mongod`
- Check your connection string in `.env`

### Port Already in Use
- Backend: Change `PORT` in `.env` (default: 5000)
- Frontend: Change port in `client/vite.config.js` (default: 5173)

### File Upload Issues
- Check that `server/uploads/` directory exists
- Verify file size is under 5MB
- Ensure file is an image (PNG, JPG, GIF, WEBP)

## Production Build

### Frontend
```bash
cd client
npm run build
```

The built files will be in `client/dist/`

### Backend
Set `NODE_ENV=production` in your `.env` file and use a process manager like PM2:

```bash
npm install -g pm2
cd server
pm2 start server.js --name photo-album
```

## Support

For issues or questions, please refer to the main README.md file.
