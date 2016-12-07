<div class="modal-wrapper">
    <div class="modal">
        <header>
            <h1>Criar um novo projeto</h1>
        </header>
        <div class="content">
            <div class="header-content">
                <?php $this->part('singles/type', ['entity' => $entity]) ?>

                <?php $this->part('singles/name', ['entity' => $entity]) ?>
            </div>

            <div class="widget">
                <h3>Descrição</h3>
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
