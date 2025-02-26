const express = require('express');
const admin = require('firebase-admin');
const dotenv = require('dotenv');
const path = require('path');
const nodemailer = require('nodemailer'); // Para enviar e-mails

dotenv.config();

// TODO: Configurar o email corretamente de preferencia o SMPT
const transporter = nodemailer.createTransport({
    service: 'gmail', // Ou outro serviço de e-mail
    auth: {
        user: 'SEU_EMAIL@gmail.com', // Substitua pelo seu e-mail
        pass: 'SUA_SENHA', // Substitua pela sua senha ou senha de app
    },
});

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

// Serve static files from the 'public' directory
app.use(express.static(path.join(__dirname, 'public')));

// Route for the main page
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.get('/confirmation-delete', (req, res) => {
    res.sendFile(path.join(__dirname, 'confirmation-delete.html'));
});

app.get('/account-deleted', (req, res) => {
    res.sendFile(path.join(__dirname, 'account-deleted.html'));
});

app.post('/request-delete', async (req, res) => {
    const { email } = req.body;
    if (!email) {
        return res.status(400).send({ error: 'Email é necessários' });
    }

    try {
        const user = await admin.auth().getUserByEmail(email);

        // TODO: criei uma rota como exemplo /delete-account
        // Crie um link de exclusão com o token
        const deletionLink = `https://SEU-SITE.com/confirm-deletion?token=${token}`;

        // Envie o e-mail de verificação de preferencia criar um html menos feio 
        const mailOptions = {
            from: 'SEU_EMAIL@gmail.com',
            to: email,
            subject: 'Confirmação de Exclusão de Conta',
            text: `Clique no link abaixo para confirmar a exclusão da sua conta:\n\n${deletionLink}`,
        };

        await transporter.sendMail(mailOptions);
        return res.status(200).json({ success: true, message: 'E-mail de verificação enviado.' });
    } catch (error) {
        // TODO: tratamento de erro para getUserByEmail email/usuario que não existe
        if (error.message.includes('EMAIL_NOT_FOUND') || error.message.includes('INVALID_PASSWORD')) {
            res.status(404).send({ error: 'Usuário não encontrado ou senha incorreta' });
        } else {
            res.status(500).send({ error: 'Falha ao excluir a conta' });
        }
    }
});

app.get('/delete-account', async (req, res) => {
    const { token } = req.query;

    if (!token) {
        return res.status(400).json({ error: 'Token inválido.' });
    }

    try {
        // Verifique o token (opcional: você pode adicionar validações adicionais)
        const email = await admin.auth().verifyIdToken(token);

        // Exclua o usuário do Firebase Authentication
        const user = await admin.auth().getUserByEmail(email);
        await admin.auth().deleteUser(user.uid);

        // TODO: implementar exclusão
        // await admin.firestore().collection('users').doc(user.uid).delete();

        return res.status(200).json({ success: true, message: 'Conta excluída com sucesso.' });
    } catch (error) {
        console.error('Erro ao excluir conta:', error);
        return res.status(500).json({ error: 'Erro ao excluir conta.' });
    }
});


const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
