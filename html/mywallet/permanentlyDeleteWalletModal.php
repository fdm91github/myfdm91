<!-- Modale per l'eliminazione di una spesa extra -->
<div class="modal fade" id="permanentlyDeleteWalletModal" tabindex="-1" aria-labelledby="permanentlyDeleteWalletModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permanentlyDeleteWalletModalLabel">Elimina Portafogli</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="permanentlyDeleteWalletStatus"></div>
                <p>Sei sicuro di voler eliminare DEFINITIVAMENTE questo Portafogli?</p>
                <form id="permanentlyDeleteWalletForm" method="POST" action="permanentlyDeleteWallet.php">
                    <input type="hidden" name="id" id="permanentlyDeleteWalletId">
                    <button type="submit" class="btn btn-danger w-100">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
		$('#permanentlyDeleteWalletModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');

			$('#permanentlyDeleteWalletId').val(id);
		});

        $('#permanentlyDeleteWalletForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'permanentlyDeleteWallet.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#permanentlyDeleteWalletStatus').html('<div class="alert alert-success">Portafogli eliminato con successo.</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#permanentlyDeleteWalletStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
		});
    });
</script>
