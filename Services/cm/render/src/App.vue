<template>
  <div id="animatorCanvasContainer" style="z-index: 999999;">
        <div id="animatorCanvas" style="z-index: 999999;"></div>
        <button id="playIt" @click="play">Play</button>
        <button id="nextFrame" @click="next">next</button>
        <input id='allFrame' type="number">
    </div>
</template>

<script>
	import animEngine from './Animator/AnimatorEngine'

	export default {
		methods: {
			resizeLayout(){
				animEngine.resize(true);
			},
			play(){
				animEngine.player.play(false);
			},
			next(){
				animEngine.player.nextFrame();
			}
		},
		mounted: function(){
			$("#allFrame").val(-2);
            axios.defaults.headers.common['Authorization'] = `Bearer ${window.apiData.token}`;
			if(window.apiData){
				axios.get(`https://api.centrumklubu.pl/animations/${window.apiData.id}`)
					.then((response)=>{
						console.log(response)
                        const watermark = (response.data.watermark && response.data.watermark.length > 3) ? JSON.parse(response.data.watermark) : null;
                        animEngine.build(true,{watermark: watermark, name: response.data.name, pathField:response.data.path_field , frameData: JSON.parse(response.data.frame_data), objectInAnimation: JSON.parse(response.data.object_in_animation)});
						$(window).on('resize',this.resizeLayout);
						$("#allFrame").val(-1);
					})
					.catch((response)=>{
						console.log(response)
					})
			}
		}
	}
</script>
<style scoped>
	#animatorCanvasContainer{
		width: 1200px;
		height: 720px;
		position: fixed;
		top: 0;
		z-index: 1000;
		left: 0;
	}
	#animatorCanvas{
		width: 1200px;
		height: 720px;
		position: fixed;
		top: 0;
		z-index: 99999;
		left: 0;
	}
</style>