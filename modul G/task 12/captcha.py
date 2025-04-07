from PIL import Image, ImageDraw, ImageFont, ImageFilter
import random
import string


def generate_captcha_image(width=200, height=80, font_size=30, chars=4, noise_lines=3, noise_points=3):
    # 1. Generate random characters
    captcha_text = ''.join(random.choices(string.ascii_uppercase + string.digits, k=chars))

    # 2. Create a blank image
    image = Image.new('RGB', (width, height), color='white')
    draw = ImageDraw.Draw(image)

    # 3. Choose a font (you might need to specify the correct path)
    try:
        font = ImageFont.truetype("arial.ttf", size=font_size)  # Replace with your font path
    except IOError:
        font = ImageFont.load_default() # Fallback to default font

    # 4. Draw characters with random rotation and position
    char_width = width // chars  # Approximate width per character
    for i, char in enumerate(captcha_text):
        angle = random.randint(-20, 20)  # Slight rotation
        x = i * char_width + random.randint(5, 15)  # Random horizontal position
        y = random.randint(10, height - font_size - 10)  # Random vertical position

        # Rotate the character using a separate image
        char_image = Image.new('RGBA', (width, height), (0, 0, 0, 0))  # Transparent background
        char_draw = ImageDraw.Draw(char_image)
        char_draw.text((x, y), char, font=font, fill='black')

        rotated_char = char_image.rotate(angle, resample=Image.BICUBIC) # Rotate with smooth resampling
        image.paste(rotated_char, mask=rotated_char.split()[3])  # Paste with transparency



    # 5. Draw random lines
    for _ in range(noise_lines):
        x1 = random.randint(0, width)
        y1 = random.randint(0, height)
        x2 = random.randint(0, width)
        y2 = random.randint(0, height)
        draw.line((x1, y1, x2, y2), fill='gray', width=2)

    # 6. Draw random noise points
    for _ in range(noise_points):
        x = random.randint(0, width)
        y = random.randint(0, height)
        draw.point((x, y), fill='black')  # Mixed noise: some points are black
        draw.point((x + 1, y + 1), fill='gray') # And some are gray (slightly offset for more visual variation)


    # 7. Apply a slight blur (optional, but often makes it harder for bots)
    image = image.filter(ImageFilter.BLUR)

    return image, captcha_text


if __name__ == '__main__':
    captcha_image, captcha_text = generate_captcha_image()
    print(f"CAPTCHA text: {captcha_text}")
    captcha_image.show()  # Display the image (requires a viewer)
    # captcha_image.save("captcha.png") # Save the image (optional)
