const express = require('express');
const admin = require('firebase-admin');
const dotenv = require('dotenv');
const path = require('path');
const crypto = require('crypto');

dotenv.config();

const app = express();
app.use(express.json());

const serviceAccount = {
    projectId: process.env.FIREBASE_PROJECT_ID,
    clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
    privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'),
};

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
});

app.use(express.static(path.join(__dirname, 'public')));

app.post('/request-delete', async (req, res) => {
    const { email } = req.body;
    if (!email) {
        return res.status(400).send({ error: 'Email is required' });
    }

    try {
        const userRecord = await admin.auth().getUserByEmail(email);
        const token = crypto.randomBytes(20).toString('hex');
        const confirmationLink = `http://localhost:3000/confirm-delete?token=${token}&email=${encodeURIComponent(email)}`;

        console.log(`Confirmation link: ${confirmationLink}`);

        res.send({ message: 'Confirmation email sent', link: confirmationLink });
    } catch (error) {
        console.error('Error:', error);
        if (error.code === 'auth/user-not-found') {
            res.status(404).send({ error: 'User not found' });
        } else {
            res.status(500).send({ error: 'Failed to send confirmation email' });
        }
    }
});

app.get('/confirm-delete', async (req, res) => {
    const { token, email } = req.query;

    if (!token || !email) {
        return res.status(400).send({ error: 'Token and email are required' });
    }

    try {
        const userRecord = await admin.auth().getUserByEmail(email);
        await admin.auth().deleteUser(userRecord.uid);
        res.redirect(`/page3.html?email=${encodeURIComponent(email)}`);
    } catch (error) {
        console.error('Error:', error);
        if (error.code === 'auth/user-not-found') {
            res.status(404).send({ error: 'User not found' });
        } else {
            res.status(500).send({ error: 'Failed to delete account' });
        }
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});