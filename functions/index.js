/**
 * Import function triggers from their respective submodules:
 *
 * const {onCall} = require("firebase-functions/v2/https");
 * const {onDocumentWritten} = require("firebase-functions/v2/firestore");
 *
 * See a full list of supported triggers at https://firebase.google.com/docs/functions
 */

const { onRequest } = require("firebase-functions/v2/https");
const logger = require("firebase-functions/logger");
const admin = require('firebase-admin');
const dotenv = require('dotenv');
const path = require('path');
const nodemailer = require('nodemailer'); // Para enviar e-mails

// Create and deploy your first functions
// https://firebase.google.com/docs/functions/get-started

dotenv.config();

// TODO: Configurar o email corretamente de preferencia o SMPT
const transporter = nodemailer.createTransport({
    service: 'gmail', // Ou outro serviço de e-mail
    auth: {
        user: 'SEU_EMAIL@gmail.com', // Substitua pelo seu e-mail
        pass: 'SUA_SENHA', // Substitua pela sua senha ou senha de app
    },
});

const serviceAccount = {
    projectId: process.env.FIREBASE_PROJECT_ID,
    clientEmail: process.env.FIREBASE_CLIENT_EMAIL,
    privateKey: process.env.FIREBASE_PRIVATE_KEY.replace(/\\n/g, '\n'),
};

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
});

exports.helloWorld = onRequest((request, response) => {
    logger.info("Hello logs!", { structuredData: true });
    response.send("Hello from Firebase!");
});

exports.requestDelete = onRequest(async (req, res) => {
    const { email } = req.body;
    if (!email) {
        return res.status(400).json({ error: 'Email é necessários' });
    }
    try {
        const token = await admin.auth().createCustomToken(email);

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
            res.status(404).json({ error: 'Usuário não encontrado ou senha incorreta' });
        } else {
            res.status(500).json({ error: 'Falha ao excluir a conta' });
        }
    }
});

exports.deleteAccount = onRequest(async (req, res) => {
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
