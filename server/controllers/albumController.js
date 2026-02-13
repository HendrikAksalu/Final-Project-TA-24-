import Album from '../models/Album.js';
import Photo from '../models/Photo.js';

// Get all albums
export const getAllAlbums = async (req, res) => {
  try {
    const albums = await Album.find().sort({ createdAt: -1 });
    res.json(albums);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Create a new album
export const createAlbum = async (req, res) => {
  try {
    const { name, description, coverImage } = req.body;
    const album = new Album({
      name,
      description,
      coverImage
    });
    const newAlbum = await album.save();
    res.status(201).json(newAlbum);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

// Get a specific album
export const getAlbumById = async (req, res) => {
  try {
    const album = await Album.findById(req.params.id);
    if (!album) {
      return res.status(404).json({ message: 'Album not found' });
    }
    res.json(album);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Delete an album
export const deleteAlbum = async (req, res) => {
  try {
    const album = await Album.findById(req.params.id);
    if (!album) {
      return res.status(404).json({ message: 'Album not found' });
    }
    
    // Delete all photos in the album
    await Photo.deleteMany({ albumId: req.params.id });
    
    await Album.findByIdAndDelete(req.params.id);
    res.json({ message: 'Album and associated photos deleted' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
