# Minnow

Minnow is a self-hosted local media streaming website built with PHP, JavaScript, and Tailwind CSS. It indexes your personal directory and plays media directly in your web browser.

## Why Use it?

It has a modern and responsive (for the most part, not fully responsive on mobile yet.) design.

No database required. It scans your specified directory.

Minnow dynamically displays specified images as posters for the corresponding media.

## Installation

1. Clone the Repo and install PHP

Move all the project files into your project root folder.

2. Create the Directory

Navigate to the project directory and create a folder with a suitable name. (e.g. /Media).

3. Configure Your Path

Open `config.php` in any editor and point the media_path variable to the directory you just made. (e.g. /Media).

I recommend you have the media directory in the same path as your project. I have not tested it in any other way but I do not think it works.

4. Set Up Your Server

You have multiple options here.

The easiest is with PHP's built-in server:
```bash
# Navigate to your project directory and then run:
php -S localhost:8000
```
Then open http://localhost:8000 in your web browser.

Your second option is any other server that can handle PHP.
Personally I used nginx but i will not include instructions for that. You will have to research that yourself.

5. Add Your Media

Move your supported video files (supported formats for all files are listed in `config.php`.) into the directory specified earlier in `config.php`. To add images/posters to your media, simply add a supported image file into the same directory with the exact same name as the video file. (e.g. video1.mp4 and video1.jpg).

6. Enjoy!

### Note
If you know how to add your own formats/extensions then feel free. I tried to make it as easy as possible to do.
Also, I mentioned before that it is not fully responsive on mobile. It technically works on mobile and smaller screens but it is not as easy to use. I havent tried much to make it responsive. I designed it to work on my 1920x1080 display.
