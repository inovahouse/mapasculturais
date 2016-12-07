<div class="modal-wrapper">
    <div class="modal">
        <header>
            <h1>Criar um novo evento</h1>
        </header>
        <div class="content">
            <div class="header-content">
                <div class="entity-type event-type">
                    <div class="icon icon-event"></div>
                    <a href="#">Evento</a>
                </div>

                <?php $this->part('singles/name', ['entity' => $entity]) ?>
            </div>

            <div class="widget">
                <h3>Classificação Etária:</h3>
                <?php if ($this->isEditable() || $entity->classificacaoEtaria): ?>
                    <?php
                    /*Agente padrão da Giovanna editando atrações da Virada*/
                    if(!$entity->classificacaoEtaria && $entity->project && $entity->project->id == 4 && $entity->owner->id == 428){
                        $entity->classificacaoEtaria = 'Livre';
                    }
                    ?>
                    <p><span class="label <?php echo ($entity->isPropertyRequired($entity,"classificacaoEtaria") && $editEntity? 'required': '');?>"></span><span class="js-editable" data-edit="classificacaoEtaria" data-original-title="Classificação Etária" data-emptytext="Informe a classificação etária do evento"><?php echo $entity->classificacaoEtaria; ?></span></p>
                <?php endif; ?>
            </div>

            <div class="widget">
                <h3>Linguagens</h3>
                <?php if ($this->isEditable()): ?>
                    <span id="term-linguagem" class="js-editable-taxonomy" data-original-title="Linguagens" data-emptytext="Selecione pelo menos uma linguagem" data-restrict="true" data-taxonomy="linguagem"><?php echo implode('; ', $entity->terms['linguagem']) ?></span>
                <?php else: ?>
                    <?php $linguagens = array_values($app->getRegisteredTaxonomy($entity->getClassName(), 'linguagem')->restrictedTerms); sort($linguagens); ?>
                    <?php foreach ($linguagens as $i => $t): if(in_array($t, $entity->terms['linguagem'])): ?>
                        <a class="tag tag-event" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(linguagens:!(<?php echo $i ?>)),global:(enabled:(event:!t),filterEntity:event))"><?php echo $t ?></a>
                    <?php endif; endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="widget">
                <h3>Descrição curta</h3>
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning">O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui <?php echo strlen($entity->shortDescription) ?> caracteres. Você deve alterar seu texto ou este será cortado ao salvar.</div>
                <?php endif; ?>

                <p>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"shortDescription") && $editEntity? 'required': '');?>" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
            </div>
        </div>
        <footer>
            <button type="button" class="btn btn-default">Cancelar</button>
            <button type="submit">Criar</button>
        </button>
        </footer>
    </div>
</div>
