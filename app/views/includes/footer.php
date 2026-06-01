 </div>

<footer class="footer">
	<?php // Statische contactgegevens onderaan elke pagina. ?>
	<div class="footer-midden">
		<p>Aurora Theater</p>
		<p>Theaterplein 1</p>
		<p>1000 AA Amsterdam</p>
		<p>&#128222; 020 1234567</p>
		<p><a href="mailto:info@aurora-theater.nl">info@aurora-theater.nl</a></p>

		<p style="margin-top:20px;">&#128336; Openingstijden: Di - Za 18:00 - 23:00</p>

		<div class="footer-logo">
			<span class="logo-fit">AURORA</span><span class="logo-for">Theater</span>
		</div>
		<p class="copyright">&copy; <?= date('Y') ?> Aurora Theater &ndash; Alle rechten voorbehouden</p>
	</div>
</footer>
<?php // Twemoji zet unicode-emoji om naar consistente SVG's in de hele site. ?>
<script src="<?= URLROOT ?>js/twemoji.min.js"></script>
<script>twemoji.parse(document.body, { folder: 'svg', ext: '.svg' });</script>
<?php // Algemene UI-scripts (menu, dropdown, wachtwoordtoggle). ?>
<script src="<?= URLROOT ?>js/script.js"></script>
</body>
</html>


