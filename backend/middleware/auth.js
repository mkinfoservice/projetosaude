const jwt = require('jsonwebtoken');
module.exports = (roles = []) => (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ error: 'Token ausente' });
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    if (roles.length && !roles.includes(decoded.perfil)) return res.status(403).json({ error: 'Acesso negado' });
    req.user = decoded;
    next();
  } catch { res.status(401).json({ error: 'Token inválido' }); }
};