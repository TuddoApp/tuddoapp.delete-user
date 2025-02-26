const express = require('express');
const admin = require('firebase-admin');
const dotenv = require('dotenv');
const path = require('path');
const fetch = require('node-fetch');

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

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.get('/confirmation-delete', (req, res) => {
    res.sendFile(path.join(__dirname, 'page2.html'));
});

app.get('/account-deleted', (req, res) => {
    res.sendFile(path.join(__dirname, 'page3.html'));
});

app.post('/request-delete', async (req, res) => {
    const { email, password } = req.body;
    if (!email || !password) {
        return res.status(400).send({ error: 'Email e senha são necessários' });
    }

    try {
        const response = await fetch('https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' + process.env.FIREBASE_API_KEY, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email,
                password,
                returnSecureToken: true
            })
        });

        const data = await response.json();

        if (data.error) {
            throw new Error(data.error.message);
        }

        await admin.auth().deleteUser(data.localId); // Exclui o usuário
        res.send({ message: 'Conta excluída com sucesso.' });
    } catch (error) {
        console.error('Error:', error);
        if (error.message.includes('EMAIL_NOT_FOUND') || error.message.includes('INVALID_PASSWORD')) {
            res.status(404).send({ error: 'Usuário não encontrado ou senha incorreta' });
        } else {
            res.status(500).send({ error: 'Falha ao excluir a conta' });
        }
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
