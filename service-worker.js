self.addEventListener('install', (e) => {
  console.log('Service Worker Installed');
});

self.addEventListener('fetch', (e) => {
  // এটি সাধারণ রিকোয়েস্ট হ্যান্ডেল করবে
});