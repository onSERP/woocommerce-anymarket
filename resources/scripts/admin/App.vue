<template>
	<div id="anymarket__app">
		<router-view />
	</div>
</template>

<script>
export default {
	name: 'App',
}
</script>

<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

/* stylelint-disable */
@tailwind base;
@tailwind components;
@tailwind utilities;
/* stylelint-enaable */

#anymarket__app {
	@apply font-sans text-black;

	@media (max-width: 1023px) {
		.container {
			width: 100% !important;
			max-width: 100% !important;
		}
	}
}

.title {
	@apply text-2xl font-bold text-black;
}

.btn {
	@apply inline-block bg-custom-blue px-3 py-4 text-white border-0 rounded-md;

	&:hover {
		@apply text-white;
	}
}

.btn-base {
	@apply text-base;
}

.form-group {
	@apply flex flex-wrap mb-6 text-base items-center;

	label {
		@apply inline-block w-56 text-right pr-8;

		@media (max-width: 1130px) {
			@apply w-4/12 text-left  mb-2;
		}

		@media (max-width: 611px) {
			@apply w-full;
		}
	}

	.input {
		width: 590px;

		@media (max-width: 611px) {
			@apply w-full;
		}
	}

	button[type='submit'] {
		margin-left: 460px;
		@media (max-width: 611px) {
			@apply ml-auto;
		}
	}
}

input[type='text'] {
	@apply w-full py-1 bg-white border border-gray-100 rounded-md shadow;

	&:read-only {
		@apply bg-gray-300 border-gray-300;
	}
}

.help-text {
	@apply text-xs text-gray-700 mt-1;

	a {
		@apply cursor-pointer text-blue-400;
	}
}

.input-copy {
	@apply relative;

	.btn {
		@apply absolute py-2 right-0 mr-4 mt-1;
	}
}
</style>
