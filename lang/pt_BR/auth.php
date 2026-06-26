<?php

declare(strict_types = 1);

return [
    'failed'                  => 'As credenciais indicadas não coincidem com as registradas no sistema.',
    'password'                => 'A senha está errada.',
    'throttle'                => 'O número limite de tentativas de login foi atingido. Por favor, tente novamente dentro de :seconds segundos.',
    'unsupported_media_type'  => 'O Content-Type deve ser application/json.',
    'malformed_json'          => 'Payload JSON malformado.',
    'email_verification_code' => [
        'subject'          => 'Código de verificação de e-mail',
        'greeting'         => 'Olá :name!',
        'intro'            => 'Use o código abaixo para confirmar seu e-mail e concluir seu cadastro.',
        'code'             => 'Seu código de verificação é: **:code**',
        'expires_in'       => 'Este código expira em 15 minutos.',
        'ignore'           => 'Se você não solicitou este cadastro, ignore este e-mail.',
        'salutation'       => 'Atenciosamente',
        'resent_success'   => 'Código de verificação reenviado com sucesso.',
        'verified_success' => 'E-mail verificado com sucesso.',
        'invalid_code'     => 'Código de verificação inválido ou expirado.',
    ],
    'change_password' => [
        'subject'            => 'Sua senha foi alterada — :app',
        'greeting'           => 'Olá, :name!',
        'changed_at'         => 'Sua senha foi alterada com sucesso em :datetime.',
        'warning'            => 'Se você não realizou esta alteração, entre em contato imediatamente com nosso suporte ou redefina sua senha:',
        'action'             => 'Redefinir minha senha',
        'salutation'         => 'Equipe :app',
        'same_as_current'    => 'A nova senha deve ser diferente da senha atual.',
        'updated_success'    => 'Senha alterada com sucesso.',
        'email_not_verified' => 'Seu e-mail precisa ser verificado para alterar a senha.',
    ],
    'password_reset_code' => [
        'subject'                  => 'Código para redefinir sua senha',
        'greeting'                 => 'Olá :name!',
        'intro'                    => 'Use o código abaixo para redefinir sua senha do aplicativo.',
        'code'                     => 'Seu código de redefinição é: **:code**',
        'expires_in'               => 'Este código expira em 15 minutos.',
        'ignore'                   => 'Se você não solicitou redefinição de senha, ignore este e-mail.',
        'salutation'               => 'Atenciosamente',
        'sent_success'             => 'Código de recuperação enviado com sucesso.',
        'verified_success'         => 'Código de recuperação validado com sucesso.',
        'invalid_code'             => 'Código de recuperação inválido ou expirado.',
        'password_updated_success' => 'Senha redefinida com sucesso.',
    ],
];
