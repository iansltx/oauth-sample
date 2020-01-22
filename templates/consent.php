<?php
    /** @var $user App\Models\User */
    /** @var $authRequest League\OAuth2\Server\RequestTypes\AuthorizationRequest */
    /** @var $requestedScopes string[] */
?>
<h1>Access Request</h1>
<form method="post">
    <p>Hi <?= $user->getFirstName() . ' ' . $user->getLastName() ?>!</p>
    <p><?= $authRequest->getClient()->getName() ?> would like to access your account with the following permissions:</p>
    <ul>
        <?php foreach ($requestedScopes as $scope): ?>
            <li><?= $scope ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="submit" name="consent" value="approve" class="btn btn-success">Approve</button>
    <button type="submit" name="consent" value="deny" class="btn btn-danger">Deny</button>
</form>

