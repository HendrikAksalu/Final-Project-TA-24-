import express from 'express';
import {
  getAllPhotos,
  uploadPhoto,
  deletePhoto
} from '../controllers/photoController.js';
import upload from '../middleware/upload.js';

const router = express.Router();

router.get('/', getAllPhotos);
router.post('/', upload.single('photo'), uploadPhoto);
router.delete('/:id', deletePhoto);

export default router;
