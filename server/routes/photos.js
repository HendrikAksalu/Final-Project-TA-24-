import express from 'express';
import {
  getAllPhotos,
  uploadPhoto,
  deletePhoto
} from '../controllers/photoController.js';
import upload from '../middleware/upload.js';
import { apiLimiter, uploadLimiter } from '../middleware/rateLimiter.js';

const router = express.Router();

router.get('/', apiLimiter, getAllPhotos);
router.post('/', uploadLimiter, upload.single('photo'), uploadPhoto);
router.delete('/:id', apiLimiter, deletePhoto);

export default router;
