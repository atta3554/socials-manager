const items = [];

document.querySelectorAll('.social-icon canvas').forEach(canvas=> {

  const renderer          = new THREE.WebGLRenderer({canvas, antialias: true, alpha: true});

  const scene             = new THREE.Scene();
  const camera            = new THREE.PerspectiveCamera(35, canvas.clientWidth/canvas.clientHeight, 0.1, 400);

  const textureUrl        = canvas.dataset.icon;
  const texture           = new THREE.TextureLoader().load(textureUrl, tex=> configTexture(tex));
  
  const frontMaterial     = new THREE.MeshStandardMaterial({ map: texture, roughness: 0.2, metalness: 0.8});
  const sideMaterial      = new THREE.MeshPhysicalMaterial({color: generate_color(canvas.id), clearcoat: 1, clearcoatRoughness: 0.1, roughness: 0.2, metalness: 0.8});
  const socialMaterial    = [sideMaterial, sideMaterial, sideMaterial, sideMaterial, frontMaterial, frontMaterial];
  const socialGeo         = new THREE.BoxGeometry(5, 5, 0.6, 4, 4, 4);
  const socialMesh        = new THREE.Mesh(socialGeo, socialMaterial);

  const directionalLight  = new THREE.DirectionalLight(0xffffff, 2);
    
  renderer.setClearColor(0x000000, 0);
  renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  directionalLight.position.set(2, 1, 5);
  camera.position.z = 10;

  texture.encoding  = THREE.sRGBEncoding;
  
  scene.background  = null;
  scene.add(directionalLight, socialMesh);

  items.push({renderer, scene, camera, socialMesh, canvas})
})

function animate() {
  requestAnimationFrame(animate)
  
  items.forEach(item=> {
    item.socialMesh.rotation.y += 0.008;
    item.socialMesh.position.y  = Math.cos(item.socialMesh.rotation.y * 2) * 0.15;
    item.socialMesh.rotation.x  = Math.sin(item.socialMesh.rotation.y) * 0.08;

    item.renderer.render(item.scene, item.camera);
  })
}

function generate_color(social) {
  switch(social) {
    case 'instagram' :
      return 0xE1306C;

    case 'youtube' :
      return 0xFF0000

    case 'snapchat' :
      return 0xffda00

    case 'tiktok' :
      return 0x000000

    case 'google' :
      return 0x4285F4

      default: 
      return 0xffffff;
  }
}

function configTexture(tex) {
  tex.colorSpace = THREE.SRGBColorSpace;
  tex.wrapS = THREE.ClampToEdgeWrapping;
  tex.wrapT = THREE.ClampToEdgeWrapping;

  tex.center.set(.5, .5)
  tex.repeat.set(.6, .6)

  tex.needsUpdate = true;
}

animate()