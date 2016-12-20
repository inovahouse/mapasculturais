<nav id="about-nav" class="alignright clearfix">
    <h1 id="organization-logo">
        <a href="#" target="_blank">
        <?php if($this->subsiteInstance->getInstitute()): ?>
            <img src="<?php echo $this->subsiteInstance->institute->transform('logoHeader')->url;?>" />
        <?php else:?>
            <img src="<?php $this->asset('img/logo-instituicao.png'); ?>" />
        <?php endif;?>
        </a>
    </h1>
    <ul id="secondary-menu">
        <li><a class="icon icon-about hltip" href="<?php echo $app->createUrl('site', 'page', array('sobre')) ?>" title="Sobre o Site"></a></li>
        <li><a class="icon icon-help hltip" href="<?php echo $app->createUrl('site', 'page', array('como-usar')) ?>" title="Como usar"></a></li>
    </ul>
</nav>
