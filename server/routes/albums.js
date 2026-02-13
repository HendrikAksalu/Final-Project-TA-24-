import express from 'express';
import {
  getAllAlbums,
  createAlbum,
  getAlbumById,
  deleteAlbum
} from '../controllers/albumController.js';
import { apiLimiter, createLimiter } from '../middleware/rateLimiter.js';

const router = express.Router();

router.get('/', apiLimiter, getAllAlbums);
router.post('/', createLimiter, createAlbum);
router.get('/:id', apiLimiter, getAlbumById);
router.delete('/:id', apiLimiter, deleteAlbum);

export default router;
