<!-- Modale per l'eliminazione di una voce delle spese -->
<div class="modal fade" id="deleteWalletDataModal" tabindex="-1" aria-labelledby="deleteWalletDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWalletDataModalLabel">Elimina spesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteWalletDataStatus"></div>
                <p>Sei sicuro di voler eliminare questa spesa?</p>
                <form id="deleteWalletDataForm" method="POST" action="deleteWalletData.php">
                    <input type="hidden" name="id" id="deleteWalletDataId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#deleteWalletDataModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            $('#deleteWalletDataId').val(id);
        });

        $('#deleteWalletDataForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteWalletData.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteWalletDataStatus').html('<div class="alert alert-success">Spesa eliminata con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteWalletDataStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
