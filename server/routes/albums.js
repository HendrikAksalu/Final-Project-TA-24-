import express from 'express';
import {
  getAllAlbums,
  createAlbum,
  getAlbumById,
  deleteAlbum
} from '../controllers/albumController.js';

const router = express.Router();

router.get('/', getAllAlbums);
router.post('/', createAlbum);
router.get('/:id', getAlbumById);
router.delete('/:id', deleteAlbum);

export default router;
