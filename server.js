import express from 'express';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

// ES modules için __dirname alternatifi
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Express uygulaması oluştur
const app = express();
const PORT = process.env.PORT || 3000;

// Güvenlik ve performans için middleware'ler
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Statik dosya servisi - assets klasörü
app.use('/assets', express.static(join(__dirname, 'assets'), {
    maxAge: process.env.NODE_ENV === 'production' ? '1y' : '0',
    etag: true,
    lastModified: true
}));

// Ana sayfa route - index.html
app.get('/', (req, res) => {
    res.sendFile(join(__dirname, 'index.html'));
});

// API endpoint - Örnek API route
app.get('/api/health', (req, res) => {
    res.json({
        status: 'OK',
        message: 'Swix Dashboard Node.js sunucusu çalışıyor! 🎉',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        version: '1.0.0'
    });
});

// API endpoint - Proje bilgileri
app.get('/api/info', (req, res) => {
    res.json({
        name: 'Swix Dashboard',
        description: 'Modern Node.js web dashboard',
        theme: 'Vuexy inspired',
        tech: ['Node.js', 'Express', 'HTML5', 'CSS3', 'JavaScript ES6+'],
        features: [
            'Responsive Design',
            'Modern UI Components', 
            'Interactive Animations',
            'GitHub Pages Compatible',
            'Hostinger Ready'
        ],
        author: 'mamirace',
        github: 'https://github.com/mamirace/swix'
    });
});

// Catch-all route - SPA için tüm isteklerde index.html döndür
app.get('*', (req, res) => {
    // API routes'u hariç tut
    if (req.path.startsWith('/api/')) {
        return res.status(404).json({
            error: 'API endpoint not found',
            path: req.path,
            available_endpoints: ['/api/health', '/api/info']
        });
    }
    
    // Diğer tüm routes için index.html döndür
    res.sendFile(join(__dirname, 'index.html'));
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Error:', err.message);
    res.status(500).json({
        error: 'Internal Server Error',
        message: process.env.NODE_ENV === 'development' ? err.message : 'Something went wrong!'
    });
});

// 404 handler (bu middleware en sona konmalı)
app.use((req, res) => {
    res.status(404).json({
        error: 'Route not found',
        path: req.path,
        message: 'The requested resource does not exist'
    });
});

// Sunucuyu başlat
app.listen(PORT, () => {
    console.log(`
🚀 Swix Dashboard sunucusu başlatıldı!
📍 Port: ${PORT}
🌐 Local: http://localhost:${PORT}
📂 Static files: /assets
🔗 API Health: http://localhost:${PORT}/api/health
🔗 API Info: http://localhost:${PORT}/api/info
📱 Environment: ${process.env.NODE_ENV || 'development'}
⏰ Started at: ${new Date().toLocaleString('tr-TR')}
    `);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 SIGTERM received. Shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('🛑 SIGINT received. Shutting down gracefully...');
    process.exit(0);
});

export default app;