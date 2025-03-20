<!-- Modale per l'eliminazione di una spesa extra -->
<div class="modal fade" id="deleteWalletModal" tabindex="-1" aria-labelledby="deleteWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteWalletModalLabel">Elimina Portafogli</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="deleteWalletStatus"></div>
                <p>Sei sicuro di voler eliminare questo Portafogli?</p>
                <form id="deleteWalletForm" method="POST" action="deleteWallet.php">
                    <input type="hidden" name="id" id="deleteWalletId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#deleteWalletModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#deleteWalletId').val(id);
		});

        $('#deleteWalletForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'deleteWallet.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#deleteWalletStatus').html('<div class="alert alert-success">Portafogli eliminato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#deleteWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
