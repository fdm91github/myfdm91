<!-- Modale per la modifica di un veicolo -->
<div class="modal fade" id="editWalletModal" tabindex="-1" aria-labelledby="editWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWalletModalLabel">Modifica Portafogli</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editWalletStatus"></div>
                <form id="editWalletForm">
                    <input type="hidden" name="id" id="editWalletId">
                    <div class="form-group">
                        <label for="editWalletDescription">Descrizione</label>
                        <input type="text" name="description" id="editWalletDescription" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editWalletModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);

            var id = button.data('id');
            var description = button.data('description');

            $('#editWalletId').val(id);
            $('#editWalletDescription').val(description);
        });

        $('#editWalletForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'editWallet.php?id=' + $('#editWalletId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editWalletStatus').html('<div class="alert alert-success">Portafogli aggiornato con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
