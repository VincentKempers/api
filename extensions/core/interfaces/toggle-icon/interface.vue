<template>
	<div>
		<i class="material-icons" @click="$emit('input', !value)">{{ value ? 'star' : 'star_border' }}</i>
	</div>
</template>

<script>
import mixin from '../../../mixins/interface';

	export default {
		mixins: [mixin],
		computed: {

		}
	}
</script>

<style type="scss" scoped>

</style>