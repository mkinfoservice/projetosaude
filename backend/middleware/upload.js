const multer = require('multer');
const path = require('path');

// Locaweb: use caminho absoluto ou relative ao public_html
const storage = multer.diskStorage({
  destination: './public/uploads/', // Crie esta pasta no seu domínio
  filename: (_, file, cb) => cb(null, `${Date.now()}-${Math.random().toString(36).slice(2)}-${file.originalname}`)
});

const fileFilter = (_, file, cb) => {
  const allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
  if (!allowed.includes(file.mimetype)) return cb(new Error('Apenas PDF, JPG ou PNG.'), false);
  cb(null, true);
};

module.exports = multer({ 
  storage, 
  fileFilter, 
  limits: { fileSize: 5 * 1024 * 1024 } // 5MB
});