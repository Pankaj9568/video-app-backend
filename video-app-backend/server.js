const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

// Database connection (replace with your RDS endpoint)
const db = mysql.createConnection({
    host: 'database-1.cdai264o6nbx.eu-north-1.rds.amazonaws.com', // RDS endpoint
    user: 'admin', // RDS username
    password: 'pankaj9639977', // RDS password
    database: 'video_sharing_app', // Database name
});

db.connect((err) => {
    if (err) throw err;
    console.log('MySQL connected');
});

// API to fetch videos with pagination
app.get('/videos', (req, res) => {
    const { page = 1, limit = 10 } = req.query;
    const offset = (page - 1) * limit;

    const query = `
        SELECT * FROM videos 
        WHERE status = 'approved' 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    `;

    db.query(query, [parseInt(limit), parseInt(offset)], (err, results) => {
        if (err) {
            console.error(err);
            return res.status(500).json({ error: 'Database error' });
        }
        res.json(results);
    });
});

// Start server
const PORT = 5000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});