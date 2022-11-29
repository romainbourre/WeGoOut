<html>


<body>

<p>Bonjour,</p>
<p><?= $connectedUser->getName() ?> vous a invité à participer à son évènement <?= $event->getTitle() ?>.</p>
<p>Pour y accéder, cliquez sur le lien ci-dessous et inscrivez-vous sur <?= $applicationName ?> !</p>
<a href="<?= $link ?>"><?= $event->getTitle() ?></a>

</body>

</html>