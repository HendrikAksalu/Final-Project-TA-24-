import Photo from '../models/Photo.js';
import Album from '../models/Album.js';

// Get all photos (optionally filter by album)
export const getAllPhotos = async (req, res) => {
  try {
    const { albumId } = req.query;
    const filter = albumId ? { albumId } : {};
    const photos = await Photo.find(filter).sort({ createdAt: -1 });
    res.json(photos);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Upload a new photo
export const uploadPhoto = async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ message: 'No file uploaded' });
    }

    const { title, albumId } = req.body;

    // Verify album exists
    const album = await Album.findById(albumId);
    if (!album) {
      return res.status(404).json({ message: 'Album not found' });
    }

    const photo = new Photo({
      title,
      url: `/uploads/${req.file.filename}`,
      albumId
    });

    const newPhoto = await photo.save();

    // Update album cover image if it doesn't have one
    if (!album.coverImage) {
      album.coverImage = photo.url;
      await album.save();
    }

    res.status(201).json(newPhoto);
  } catch (error) {
    res.status(400).json({ message: error.message });
  }
};

// Delete a photo
export const deletePhoto = async (req, res) => {
  try {
    const photo = await Photo.findById(req.params.id);
    if (!photo) {
      return res.status(404).json({ message: 'Photo not found' });
    }

    await Photo.findByIdAndDelete(req.params.id);
    res.json({ message: 'Photo deleted' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
