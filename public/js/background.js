/**
 * Economik0 - Versión de compatibilidad total
 */
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.querySelector('#prism-canvas');
    if (!canvas) return;

    try {
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        
        const renderer = new THREE.WebGLRenderer({ 
            canvas: canvas, 
            antialias: true, 
            alpha: true,
            preserveDrawingBuffer: true 
        });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setClearColor(0x000000, 0);

        // Geometría del prisma
        const geometry = new THREE.IcosahedronGeometry(10, 1); 
        
        // USAMOS MeshNormalMaterial: No necesita luces para verse.
        // Si Chrome muestra colores, el problema previo era la luz.
        const material = new THREE.MeshNormalMaterial({ 
            transparent: true, 
            opacity: 0.7 
        });

        const prism = new THREE.Mesh(geometry, material);
        scene.add(prism);

        camera.position.z = 20;

        function animate() {
            requestAnimationFrame(animate);
            const time = Date.now() * 0.001;

            prism.rotation.y += 0.002;
            prism.rotation.x += 0.001;

            // Efecto respiración
            const scale = 1 + Math.sin(time * 0.6) * 0.15; 
            prism.scale.set(scale, scale, scale);

            renderer.render(scene, camera);
        }

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        animate();
    } catch (e) {
        console.error("WebGL Error:", e);
    }
});