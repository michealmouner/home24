<?php

namespace AppBundle\Api;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * BadCredentialsException is thrown when the user credentials are invalid.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Micheal Mouner <micheal.mouner@gmail.com>
 */
class BadCredentialsException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid credentials.';
    }
}
