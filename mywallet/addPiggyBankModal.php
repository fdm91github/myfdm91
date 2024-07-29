<!-- Modale per l'aggiunta di una voce al salvadanaio -->
<div class="modal fade" id="addPiggyBankModal" tabindex="-1" role="dialog" aria-labelledby="addPiggyBankModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPiggyBankModalLabel">Aggiungi al salvadanaio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addPiggyBankStatus"></div>
                <form id="addPiggyBankForm">
					<div class="form-group">
                        <label for="addPiggyBankDescription">Descrizione</label>
                        <input type="text" name="name" id="addPiggyBankName" class="form-control" step="0.01" placeholder="Descrizione" required>
                    </div>
                    <div class="form-group">
                        <label for="addPiggyBankAmount">Totale</label>
                        <input type="number" name="amount" id="addPiggyBankAmount" class="form-control" step="0.01" placeholder="Totale" required>
                    </div>
                    <div class="form-group">
                        <label for="addPiggyBankDate">Data</label>
                        <input type="date" name="added_date" id="addPiggyBankDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#addPiggyBankForm').submit(function(e) {
			e.preventDefault();
			var $form = $(this); // Definizione di $form
			$form.find('button[type="submit"]').prop('disabled', true); // Disable submit button
			$.ajax({
				url: 'addPiggyBank.php',
				type: 'POST',
				data: $form.serialize(),
				success: function(response) {
					$('#addPiggyBankStatus').html('<div class="alert alert-success">Importo aggiunto al salvadanaio con successo!</div>');
					$('#addPiggyBankForm')[0].reset();
					setTimeout(function() { window.location.reload(); }, 1000);
				},
				error: function() {
					$('#addPiggyBankStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				},
				complete: function() {
					$form.find('button[type="submit"]').prop('disabled', false); // Re-enable submit button
				}
			});
		});
    });
</script>