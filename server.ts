import express from 'express';
import 'dotenv/config';
import { createServer as createViteServer } from 'vite';
import path from 'path';
import multer from 'multer';
import Database from 'better-sqlite3';
import bcrypt from 'bcryptjs';
import cors from 'cors';
import nodemailer from 'nodemailer';

const db = new Database('evaluation.db');
const base = '/faculty-evaluation-system';

// Email Transporter
const transporter = nodemailer.createTransport({
  host: 'smtp.gmail.com',
  port: 465,
  secure: true, // use SSL
  auth: {
    user: process.env.SMTP_EMAIL || 'marcelinoorienza01@gmail.com',
    pass: process.env.SMTP_PASSWORD || 'yhpc urbs mctr mktn'
  }
});

// Initialize Database Schema
db.exec(`
  CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firstname TEXT,
    lastname TEXT,
    email TEXT UNIQUE,
    password TEXT,
    avatar TEXT,
    role TEXT -- superadmin, admin, faculty, student
  );

  CREATE TABLE IF NOT EXISTS academic_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    year TEXT,
    semester INTEGER,
    is_default INTEGER DEFAULT 0,
    status INTEGER DEFAULT 0 -- 0:Pending, 1:Started, 2:Closed
  );

  CREATE TABLE IF NOT EXISTS class_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    curriculum TEXT,
    level TEXT,
    section TEXT
  );

  CREATE TABLE IF NOT EXISTS subject_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT,
    subject TEXT,
    description TEXT
  );

  CREATE TABLE IF NOT EXISTS faculty_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id TEXT UNIQUE,
    firstname TEXT,
    lastname TEXT,
    email TEXT UNIQUE,
    password TEXT,
    avatar TEXT
  );

  CREATE TABLE IF NOT EXISTS student_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    school_id TEXT UNIQUE,
    firstname TEXT,
    lastname TEXT,
    email TEXT UNIQUE,
    password TEXT,
    class_id INTEGER,
    avatar TEXT
  );

  CREATE TABLE IF NOT EXISTS criteria_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    criteria TEXT,
    order_by INTEGER
  );

  CREATE TABLE IF NOT EXISTS question_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    academic_id INTEGER,
    criteria_id INTEGER,
    question TEXT,
    order_by INTEGER
  );

  CREATE TABLE IF NOT EXISTS evaluation_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    academic_id INTEGER,
    student_id INTEGER,
    faculty_id INTEGER,
    class_id INTEGER,
    subject_id INTEGER,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP
  );

  CREATE TABLE IF NOT EXISTS evaluation_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evaluation_id INTEGER,
    question_id INTEGER,
    rating INTEGER
  );

  CREATE TABLE IF NOT EXISTS evaluation_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    evaluation_id INTEGER,
    comment TEXT,
    is_published INTEGER DEFAULT 0
  );

  CREATE TABLE IF NOT EXISTS published_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    academic_id INTEGER,
    faculty_id INTEGER,
    is_published INTEGER DEFAULT 0,
    UNIQUE(academic_id, faculty_id)
  );

  CREATE TABLE IF NOT EXISTS restriction_list (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    academic_id INTEGER,
    faculty_id INTEGER,
    class_id INTEGER,
    subject_id INTEGER
  );
`);

// Seed initial users
const adminCheck = db.prepare('SELECT * FROM users WHERE role = ?').get('superadmin');
if (!adminCheck) {
  const hash = bcrypt.hashSync('password', 10);
  db.prepare('INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)').run(
    'Super', 'Admin', 'SuperAdmin01', hash, 'superadmin'
  );
  
  db.prepare('INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)').run(
    'Admin', 'User', 'Admin01', hash, 'admin'
  );
  
  db.prepare('INSERT INTO faculty_list (school_id, firstname, lastname, email, password) VALUES (?, ?, ?, ?, ?)').run(
    'FAC-001', 'John', 'Faculty', 'Faculty01', hash
  );
  
  db.prepare('INSERT INTO student_list (school_id, firstname, lastname, email, password) VALUES (?, ?, ?, ?, ?)').run(
    'STU-001', 'Jane', 'Student', 'Student01', hash
  );
  
  db.prepare('INSERT INTO academic_list (year, semester, is_default, status) VALUES (?, ?, ?, ?)').run(
    '2025-2026', 1, 1, 1
  );
}

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(cors());
  app.use(express.json({ limit: '50mb' }));
  app.use(express.urlencoded({ limit: '50mb', extended: true }));
  app.use('/uploads', express.static('uploads'));

  const storage = multer.diskStorage({
    destination: './uploads/',
    filename: (req, file, cb) => {
      cb(null, Date.now() + path.extname(file.originalname));
    }
  });
  const upload = multer({ storage });

  // Auth API
  app.post(`${base}/api/login.php`, (req, res) => {
    const { email, password } = req.body;
    // Check users table (superadmin, admin)
    let user = db.prepare('SELECT * FROM users WHERE email = ?').get(email) as any;
    let type = user?.role;

    if (!user) {
      // Check faculty table
      user = db.prepare('SELECT * FROM faculty_list WHERE school_id = ? OR email = ?').get(email, email) as any;
      if (user) type = 'faculty';
    }

    if (!user) {
      // Check student table
      user = db.prepare('SELECT * FROM student_list WHERE school_id = ? OR email = ?').get(email, email) as any;
      if (user) type = 'student';
    }

    if (user && bcrypt.compareSync(password, user.password)) {
      const academic = db.prepare('SELECT * FROM academic_list WHERE is_default = 1').get();
      res.json({ success: true, user, type, academic });
    } else {
      res.status(401).json({ success: false, message: 'Invalid credentials' });
    }
  });

  // Generic CRUD helper
  app.get(`${base}/api/:table.php`, (req, res) => {
    try {
      const data = db.prepare(`SELECT * FROM ${req.params.table}`).all();
      res.json(data);
    } catch (e) {
      res.status(500).json({ error: e.message });
    }
  });

  app.post(`${base}/api/:table.php`, (req, res) => {
    try {
      const { table } = req.params;
      const keys = Object.keys(req.body);
      const values = Object.values(req.body);
      
      // Hash password if present
      if (keys.includes('password')) {
        const passIdx = keys.indexOf('password');
        values[passIdx] = bcrypt.hashSync(values[passIdx] as string, 10);
      }

      const placeholders = keys.map(() => '?').join(',');
      const stmt = db.prepare(`INSERT INTO ${table} (${keys.join(',')}) VALUES (${placeholders})`);
      const result = stmt.run(...values);
      res.json({ success: true, id: result.lastInsertRowid });
    } catch (e) {
      res.status(500).json({ error: e.message });
    }
  });

  app.put(`${base}/api/:table/:id.php`, (req, res) => {
    try {
      const { table, id } = req.params;
      const keys = Object.keys(req.body);
      const values = Object.values(req.body);

      if (keys.includes('password')) {
        const passIdx = keys.indexOf('password');
        if (!values[passIdx]) {
          keys.splice(passIdx, 1);
          values.splice(passIdx, 1);
        } else {
          values[passIdx] = bcrypt.hashSync(values[passIdx] as string, 10);
        }
      }

      const setClause = keys.map(k => `${k} = ?`).join(',');
      const stmt = db.prepare(`UPDATE ${table} SET ${setClause} WHERE id = ?`);
      stmt.run(...values, id);
      res.json({ success: true });
    } catch (e) {
      res.status(500).json({ error: e.message });
    }
  });

  app.delete(`${base}/api/:table/:id.php`, (req, res) => {
    try {
      const { table, id } = req.params;
      db.prepare(`DELETE FROM ${table} WHERE id = ?`).run(id);
      res.json({ success: true });
    } catch (e) {
      res.status(500).json({ error: e.message });
    }
  });

  // Specific APIs
  app.get(`${base}/api/academic/default.php`, (req, res) => {
    const data = db.prepare('SELECT * FROM academic_list WHERE is_default = 1').get();
    res.json(data);
  });

  // Email API
  const sendCertificateHandler = async (req: any, res: any) => {
    const { email, name, imgData } = req.body;
    
    if (!email || !imgData) {
      return res.status(400).json({ success: false, message: 'Missing email or image data' });
    }

    try {
      // Remove header from base64 string
      const base64Data = imgData.replace(/^data:image\/jpeg;base64,/, "");
      const buffer = Buffer.from(base64Data, 'base64');

      const mailOptions = {
        from: `"Faculty Evaluation System" <${process.env.SMTP_EMAIL || 'marcelinoorienza01@gmail.com'}>`,
        to: email,
        subject: 'Certificate of Participation - Faculty Evaluation System',
        html: `
          <div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 16px;">
            <h2 style="color: #064e3b;">Congratulations, ${name}!</h2>
            <p style="color: #166534;">Thank you for participating in the Faculty Evaluation System. Attached is your Certificate of Participation.</p>
            <p style="color: #166534; font-size: 0.875rem;">Best regards,<br>Bulacan Polytechnic College</p>
          </div>
        `,
        attachments: [
          {
            filename: `Certificate_${name.replace(/\s+/g, '_')}.jpg`,
            content: buffer
          }
        ]
      };

      await transporter.sendMail(mailOptions);
      res.json({ success: true, message: 'Email sent successfully' });
    } catch (error) {
      console.error('Email error:', error);
      res.status(500).json({ success: false, message: error.message });
    }
  };

  app.post(`${base}/api/send-certificate`, sendCertificateHandler);
  app.post(`/api/send-certificate`, sendCertificateHandler);

  // Vite integration
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
      base: base + '/'
    });
    app.use(base, vite.middlewares);
  } else {
    const distPath = path.resolve('dist');
    app.use(base, express.static(distPath));
    app.get(`${base}/*`, (req, res) => res.sendFile(path.join(distPath, 'index.html')));
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running at http://localhost:${PORT}${base}`);
  });
}

startServer();
