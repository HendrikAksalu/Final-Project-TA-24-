import mongoose from 'mongoose';

const photoSchema = new mongoose.Schema({
  title: {
    type: String,
    trim: true
  },
  url: {
    type: String,
    required: true
  },
  albumId: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Album',
    required: true
  },
  createdAt: {
    type: Date,
    default: Date.now
  }
});

export default mongoose.model('Photo', photoSchema);
