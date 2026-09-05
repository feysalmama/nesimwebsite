"use client";

import { useEffect, useRef } from "react";
import * as THREE from "three";

export default function Hero3D() {
  const mountRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const mount = mountRef.current;
    if (!mount) return;

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(38, mount.clientWidth / mount.clientHeight, 0.1, 100);
    camera.position.set(0, 1.1, 7.2);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(mount.clientWidth, mount.clientHeight);
    mount.appendChild(renderer.domElement);

    // Lighting
    scene.add(new THREE.AmbientLight(0xffffff, 0.55));
    const sun = new THREE.DirectionalLight(0xf2a03f, 1.4);
    sun.position.set(4, 6, 4);
    scene.add(sun);
    const rim = new THREE.DirectionalLight(0x6fbe7d, 0.6);
    rim.position.set(-5, 2, -3);
    scene.add(rim);

    // Group that scales in on load
    const root = new THREE.Group();
    scene.add(root);

    // Globe — low-poly icosahedron
    const globeGeo = new THREE.IcosahedronGeometry(1.35, 2);
    const globeMat = new THREE.MeshStandardMaterial({
      color: 0x2d74b5,
      flatShading: true,
      roughness: 0.55,
      metalness: 0.05,
    });
    const globe = new THREE.Mesh(globeGeo, globeMat);
    globe.position.y = 1.15;
    root.add(globe);

    // Landmass speckles on the globe (small green dodecahedra)
    const landGeo = new THREE.DodecahedronGeometry(0.14, 0);
    const landMat = new THREE.MeshStandardMaterial({ color: 0x2e8b4f, flatShading: true });
    const landCount = 22;
    for (let i = 0; i < landCount; i++) {
      const land = new THREE.Mesh(landGeo, landMat);
      const phi = Math.acos(-1 + (2 * i) / landCount);
      const theta = Math.sqrt(landCount * Math.PI) * phi;
      land.position.setFromSphericalCoords(1.42, phi, theta);
      land.position.y += 1.15;
      land.scale.setScalar(0.6 + Math.random() * 0.7);
      root.add(land);
    }

    // Open book beneath the globe
    const bookGroup = new THREE.Group();
    const pageMat = new THREE.MeshStandardMaterial({ color: 0xfaf8f4, roughness: 0.8 });
    const pageGeo = new THREE.BoxGeometry(1.7, 0.06, 1.15);
    const pageL = new THREE.Mesh(pageGeo, pageMat);
    pageL.position.x = -0.86;
    pageL.rotation.z = 0.16;
    const pageR = new THREE.Mesh(pageGeo, pageMat);
    pageR.position.x = 0.86;
    pageR.rotation.z = -0.16;
    bookGroup.add(pageL, pageR);

    const spineMat = new THREE.MeshStandardMaterial({ color: 0x0f4c2a, flatShading: true });
    const spine = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.14, 1.2), spineMat);
    bookGroup.add(spine);

    bookGroup.position.y = -0.55;
    root.add(bookGroup);

    // Leaf accents — small flattened tetrahedra orbiting gently
    const leafGeo = new THREE.ConeGeometry(0.14, 0.34, 4);
    const leafMat = new THREE.MeshStandardMaterial({ color: 0xe8721c, flatShading: true });
    const leaves: THREE.Mesh[] = [];
    for (let i = 0; i < 8; i++) {
      const leaf = new THREE.Mesh(leafGeo, leafMat.clone());
      const angle = (i / 8) * Math.PI * 2;
      leaf.userData.angle = angle;
      leaf.userData.radius = 2.1 + Math.random() * 0.4;
      leaf.userData.speed = 0.15 + Math.random() * 0.1;
      leaf.userData.yOffset = Math.random() * Math.PI * 2;
      leaf.scale.setScalar(0.6 + Math.random() * 0.5);
      root.add(leaf);
      leaves.push(leaf);
    }

    root.scale.setScalar(prefersReducedMotion ? 1 : 0.001);

    let frame = 0;
    let raf: number;
    const clock = new THREE.Clock();

    function animate() {
      raf = requestAnimationFrame(animate);
      const t = clock.getElapsedTime();

      if (!prefersReducedMotion) {
        // grow-in with easing on load
        const growTarget = 1;
        root.scale.x += (growTarget - root.scale.x) * 0.06;
        root.scale.y = root.scale.z = root.scale.x;

        globe.rotation.y = t * 0.25;
        bookGroup.rotation.y = Math.sin(t * 0.4) * 0.06;
        root.position.y = Math.sin(t * 0.6) * 0.08;

        leaves.forEach((leaf) => {
          const a = leaf.userData.angle + t * leaf.userData.speed;
          const r = leaf.userData.radius;
          leaf.position.set(
            Math.cos(a) * r,
            0.4 + Math.sin(t * 0.8 + leaf.userData.yOffset) * 0.5,
            Math.sin(a) * r * 0.5 - 0.5
          );
          leaf.rotation.x = t * 0.6;
          leaf.rotation.y = t * 0.4;
        });
      }

      renderer.render(scene, camera);
      frame++;
    }
    animate();

    function handleResize() {
      if (!mount) return;
      camera.aspect = mount.clientWidth / mount.clientHeight;
      camera.updateProjectionMatrix();
      renderer.setSize(mount.clientWidth, mount.clientHeight);
    }
    window.addEventListener("resize", handleResize);

    return () => {
      cancelAnimationFrame(raf);
      window.removeEventListener("resize", handleResize);
      mount.removeChild(renderer.domElement);
      globeGeo.dispose();
      globeMat.dispose();
      landGeo.dispose();
      landMat.dispose();
      pageGeo.dispose();
      pageMat.dispose();
      spineMat.dispose();
      leafGeo.dispose();
      renderer.dispose();
    };
  }, []);

  return (
    <div
      ref={mountRef}
      className="h-[340px] w-full sm:h-[420px] lg:h-full lg:min-h-[480px]"
      aria-hidden="true"
    />
  );
}
